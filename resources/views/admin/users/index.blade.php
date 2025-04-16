@extends('layout.app')

@section('title', 'Users | Admin Dashboard')

@section('content')
    <div class="row">

        <div class="col-3">

            {{-- тут было начало блока left-sidebar --}}
            @include('admin.shared.left-sidebar')
            {{-- тут был конец блока left-sidebar --}}

        </div>
        <div class="col-9">

            <h1>Users</h1>

            <table class="table table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined At</th>
                        <th>#</th>
                    </tr>
                </thead>
                @foreach ($users as $user)
                    <tbody>
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at->toDateString() }}</td>
                            <td>
                                <a href="{{ route('users.show', $user->id) }}">View</a>
                                <a href="{{ route('users.edit', $user->id) }}">Edit</a>
                            </td>
                        </tr>

                    </tbody>
                @endforeach
            </table>
            <div>
                {{ $users->links() }}
            </div>


        </div>
    </div>
@endsection

