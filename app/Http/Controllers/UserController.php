<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // use UsersAuthorizable;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['action'] = '/user';
        $this->data['outlets'] = Outlet::where('status', true)->get();

        return view('user.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(StoreUserRequest $request, ImageService $imageService)
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $compressed = $imageService->compress($file);
            $fileName = time().'_'.uniqid().'.jpg';

            Storage::disk('public')->put('uploads/users/'.$fileName, $compressed);

            $data['foto'] = $fileName;
        } else {
            $data['foto'] = 'avatar-1.jpg';
        }

        $data['password'] = Hash::make($data['password']);

        // Remove outlets from data as it's a relation
        unset($data['outlets']);

        $user = User::create($data);
        $user->assignRole('user');

        if ($request->has('outlets') && count($request->outlets) > 0) {
            $user->outlets()->sync($request->outlets);
            $user->update(['current_outlet_id' => $request->outlets[0]]);
        }

        return redirect('/user')->with('success', 'New user has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(User $user)
    {
        $this->data['user_data'] = $user;
        $this->data['action'] = '/user/'.$user->uuid;
        $this->data['outlets'] = Outlet::where('status', true)->get();

        return view('user.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateUserRequest $request, User $user, ImageService $imageService)
    {
        // Ambil data hasil validasi
        $validatedData = $request->validated();

        // Handle foto baru
        if ($request->hasFile('foto')) {
            if ($user->foto && $user->foto !== 'avatar-1.jpg') {
                $oldFilePath = 'uploads/users/'.$user->foto;
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }

            $file = $request->file('foto');
            $compressed = $imageService->compress($file);
            $fileName = time().'_'.uniqid().'.jpg';

            Storage::disk('public')->put('uploads/users/'.$fileName, $compressed);

            $validatedData['foto'] = $fileName;
        }

        if ($request->filled('password')) {
            $validatedData['password'] = bcrypt($request->password);
        } else {
            unset($validatedData['password']);
        }

        // Remove outlets from data as it's a relation
        unset($validatedData['outlets']);

        $user->update($validatedData);

        if ($request->has('outlets')) {
            $user->outlets()->sync($request->outlets);
            if ($user->current_outlet_id && ! in_array($user->current_outlet_id, $request->outlets)) {
                $user->update(['current_outlet_id' => count($request->outlets) > 0 ? $request->outlets[0] : null]);
            } elseif (! $user->current_outlet_id && count($request->outlets) > 0) {
                $user->update(['current_outlet_id' => $request->outlets[0]]);
            }
        } else {
            $user->outlets()->detach();
            $user->update(['current_outlet_id' => null]);
        }

        return redirect('/user')->with('success', 'User has been updated!');
    }

    public function role(User $user)
    {
        $this->data['roles'] = Role::all();
        $this->data['permissions'] = $user->getAllPermissions();
        $this->data['user'] = $user;
        // return $this->data['permissions'];
        $this->data['action'] = '/user/roleaction/'.$user->uuid;

        return view('user.role', $this->data);
    }

    public function roleaction(Request $request, User $user)
    {
        $user->syncRoles($request->roles);

        return redirect('/user')->with('success', 'Roles '.$user->name.' has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $uuid
     * @return Response
     */
    public function destroy($uuid)
    {
        // Cek permission: hanya user dengan permission 'User Banned' yang bisa ban
        abort_if(Gate::denies('User Banned'), 403, 'Anda tidak memiliki izin untuk membanned user.');

        // Cari user yang akan dibanned
        $user = User::where('uuid', $uuid)->firstOrFail();

        // Cek apakah user sudah dibanned
        if ($user->banned_at) {
            return redirect()->back()->with('info', __('messages.banned'));
        }

        // Lakukan banning
        $user->update(['banned_at' => now()]);

        return redirect()->back()->with('success', __('messages.banned'));
    }
}
