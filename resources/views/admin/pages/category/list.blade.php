@extends('admin.layouts.index')

@section('content')
    <div class="col-12">
        <div class="card">
            @if ($message = Session::get('notif'))
                <div class="alert alert-primary alert-dismissible fade show" role="alert">
                    <strong>{{ $message }}</strong>
                </div>
            @endif
            <div class="card-header">
                <h3 class="card-title">Category List</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <th scope="row">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                </th>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>
                                    @if ($item->status == 'active')
                                        <h4><span class="badge bg-success text-wrap">Active
                                            </span></h4>
                                    @elseif ($item->status == 'inactive')
                                        <h4><span class="badge bg-danger text-wrap">Inactive
                                            </span></h4>
                                    @else
                                        <h4><span class="badge bg-warning text-wrap">Draft
                                            </span></h4>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('category.show', ['category' => $item->id]) }}"
                                        class="btn btn-warning">List
                                        Products</a>
                                    <a href="{{ route('category.edit', ['category' => $item->id]) }}"
                                        class="btn btn-primary">Edit</a>
                                    <form action="{{ route('category.destroy', ['category' => $item->id]) }}"
                                        class="d-inline" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
                <br>
                {{ $data->withQueryString()->links() }}
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
@endsection
