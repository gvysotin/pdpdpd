@extends('layout.app')

@section('title', 'Ideas | Admin Dashboard')

@section('content')
    <div class="row">

        <div class="col-3">

            {{-- тут было начало блока left-sidebar --}}
            @include('admin.shared.left-sidebar')
            {{-- тут был конец блока left-sidebar --}}

        </div>
        <div class="col-9">

            <h1>Ideas</h1>

            <table class="table table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Content</th>
                        <th>Created At</th>
                        <th>#</th>
                    </tr>
                </thead>
                @foreach ($ideas as $idea)
                    <tbody>
                        <tr>
                            <td>{{ $idea->id }}</td>
                            <td>
                                <a href="{{ route('users.show', $idea->user->id) }}">
                                    {{ $idea->user->name }}
                                </a>
                            </td>
                            <td>{{ $idea->content }}</td>
                            <td>{{ $idea->created_at->toDateString() }}</td>
                            <td>

                                <a href="{{ route('ideas.show', $idea->id) }}">View</a>
                                <a href="{{ route('ideas.edit', $idea->id) }}">Edit</a>

                            </td>

                        </tr>

                    </tbody>
                @endforeach
            </table>
            <div>
                {{ $ideas->links() }}
            </div>


        </div>
    </div>
@endsection
