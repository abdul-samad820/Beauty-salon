@extends('layouts.auth')


@section('title', '403 - Access Denied')

@section('content')
<div class="container py-5">
    <div class="text-center">

        <h1 class="display-1 fw-bold text-danger">
            403
        </h1>

        <h3 class="mb-3">
            Access Denied
        </h3>

        <p class="text-muted mb-4">
            You do not have permission to access this page.
        </p>

        <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">
            Go Back
        </a>

        <a href="{{ route('login') }}" class="btn btn-primary">
            Login
        </a>

    </div>
</div>
@endsection
