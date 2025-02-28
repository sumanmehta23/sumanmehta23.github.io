@extends('layouts.admin.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1>Create API Token</h1>
                <form action="{{ route('admin.apitoken.store') }}" method="POST">
                    @csrf
                    <div
                        class="form-group my-2
                    @error('name')
                        has-error
                    @enderror">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        @error('name')
                            <span
                                class="help-block
                            @error('name')
                                has-error
                            @enderror">{{ $message }}</span>
                        @enderror
                        <label for="name">Your account password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            value="{{ old('password') }}">
                        @error('password')
                            <span
                                class="help-block
                            @error('password')
                                has-error text-danger
                            @enderror">{{ $message }}</span>
                        @enderror
                        <div class="muted d-block">Please confirm your password to create new api token</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
@endsection
