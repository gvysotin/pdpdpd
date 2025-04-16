@extends('layout.app')

@section('title', 'Comments | Admin Dashboard')

@section('content')
    <div class="row">

        <div class="col-3">

            {{-- тут было начало блока left-sidebar --}}
            @include('admin.shared.left-sidebar')
            {{-- тут был конец блока left-sidebar --}}

        </div>
        <div class="col-9">

            <h1>Comments</h1>

            {{-- тут был кусок html-кода с сообщением об успешном добавлении идеи --}}
            @include('shared.success-message')


            <table class="table table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Idea</th>
                        <th>Content</th>
                        <th>Created At</th>
                        <th>#</th>
                    </tr>
                </thead>
                @foreach ($comments as $comment)
                    <tbody>
                        <tr>
                            <td>{{ $comment->id }}</td>
                            <td>
                                <a href="{{ route('users.show', $comment->user->id) }}">
                                    {{ $comment->user->name }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('ideas.show', $comment->idea->id) }}">
                                    {{ $comment->idea->id }}
                                </a>
                            </td>
                            <td>{{ $comment->content }}</td>
                            <td>{{ $comment->created_at->toDateString() }}</td>
                            <td>

                                {{-- <a href="{{ route('admin.comments.destroy', $comment->id) }}">Delete</a> --}}
                                <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST">
                                    @csrf
                                    @method('delete')
                                    <a href="#" onclick="this.closest('form').submit();return false;">Delete</a>
                                    {{-- <button type='submit'>Delete</button> --}}
                                </form>

                            </td>

                        </tr>

                    </tbody>
                @endforeach
            </table>
            <div>
                {{ $comments->links() }}
            </div>


        </div>
    </div>
@endsection
