@extends('admin.layouts.index')

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaction detail #Ntr-{{ $transaction->id }}</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <h5>Nama Pembeli : {{ $transaction->customer }}</h5>
                <h5>Alamat : {{ $transaction->address }}</h5>
                <h5>Total Harga : {{ @money($transaction->total_amount) }}</h5>
                <h5>Status :
                    @if ($status->transaction_status == 'pending')
                        Pending
                    @elseif($status->transaction_status == 'settlement')
                        Settlement
                    @elseif($status->transaction_status == 'success')
                        Success
                    @elseif($status->transaction_status == 'deny')
                        Denied
                    @elseif($status->transaction_status == 'expire')
                        Expired
                    @elseif($status->transaction_status == 'cancel')
                        Cancelled
                    @elseif($status->transaction_status == 'refund')
                        Refunded
                    @elseif($status->transaction_status == 'error')
                        Error
                    @else
                        Unknown
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>QTY</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details as $dtl )
                        <tr>
                            <th scope="row">{{$loop->iteration}}</th>
                            <td>{{ $dtl->product_id}}</td>
                            <td>{{ $dtl->product->name}}</td>
                            <td>{{ $dtl->quantity}}</td>
                            <td>{{ @money($dtl->amount)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>QTY</th>
                            <th>Total</th>
                        </tr>
                    </tfoot>
                </table>
                <br>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
@endsection
