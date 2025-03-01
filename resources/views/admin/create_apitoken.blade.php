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

        <table class="table mt-4">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tokens as $token)
                    <tr>
                        <td>{{ $token->name }}</td>
                        <td>{{ $token->created_at->format('Y-m-d') }}<br>{{ $token->created_at->format('H:i:s') }}</td>
                        <td>
                            <form action="{{ route('admin.apitoken.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this token?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
@endsection
