@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Shop 채널 운영중지 승인</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if(Session::has('success_message'))
                    <div class="alert alert-success">{{ Session::get('success_message') }}</div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <form method="GET" action="{{ route('admin.shop_channel_closures.index') }}" class="form-inline">
                            <select name="status" class="form-control mr-2">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>전체</option>
                                <option value="requested" {{ $status === 'requested' ? 'selected' : '' }}>승인대기</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>승인완료</option>
                                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>반려</option>
                                <option value="none" {{ $status === 'none' ? 'selected' : '' }}>미요청</option>
                            </select>
                            <input type="text" name="keyword" value="{{ $keyword }}" class="form-control mr-2" placeholder="채널명/코드/판매자">
                            <button type="submit" class="btn btn-primary">검색</button>
                        </form>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>판매자</th>
                                    <th>Shop 채널</th>
                                    <th>채널코드</th>
                                    <th>운영상태</th>
                                    <th>요청상태</th>
                                    <th>요청일</th>
                                    <th>메모</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($channels as $channel)
                                    <tr>
                                        <td>{{ $channels->firstItem() + $loop->index }}</td>
                                        <td>{{ $channel->vendor?->name ?? '-' }}</td>
                                        <td>{{ $channel->channel_name }}</td>
                                        <td>{{ $channel->channel_code }}</td>
                                        <td>{{ (int) $channel->status === 1 ? '운영' : '중지' }}</td>
                                        <td>
                                            @switch($channel->closure_status)
                                                @case('requested') 승인대기 @break
                                                @case('approved') 승인완료 @break
                                                @case('rejected') 반려 @break
                                                @default 미요청
                                            @endswitch
                                        </td>
                                        <td>{{ optional($channel->closure_requested_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                        <td>{{ $channel->closure_memo ?: '-' }}</td>
                                        <td>
                                            @if($channel->closure_status === 'requested')
                                                <form method="POST" action="{{ route('admin.shop_channel_closures.approve', $channel->id) }}" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">승인</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.shop_channel_closures.reject', $channel->id) }}" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">반려</button>
                                                </form>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">운영중지 요청 내역이 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix">
                        {{ $channels->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
