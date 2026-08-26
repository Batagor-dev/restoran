<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Shared data container passed to views.
     */
    public array $data = [];
}
