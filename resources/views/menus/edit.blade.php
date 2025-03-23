@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Menu') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-info">
                        {{--  <div class="card-header">
                            <h3 class="card-title">Color &amp; Time Picker</h3>
                        </div>  --}}

                        <div class="card-body">
                            <form action="{{route('menus.update', $menu->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label>Title:</label>
                                    <input type="text" class="form-control" name="title" value="{{$menu->title}}">
                                </div>
                                <div class="form-group">
                                    <label>URL:</label>
                                    <input type="text" class="form-control" name="url" value="{{$menu->url}}">
                                </div>
                                <div class="form-group">
                                    <label>Icon:</label>
                                    <input type="text" class="form-control" name="icon" value="{{$menu->icon}}">
                                </div>
                                <div class="form-select">
                                    <label>Parent:</label>
                                    <select name="parent_id" class="custom-select">
                                        @if(!empty($menu->parent_id))
                                            <option value="{{ $menu->parent_id }}" selected>{{ $menu->parent->title }}</option>
                                        @else
                                            <option value="">Select Parent</option>
                                        @endif
                                        @foreach($parents as $parent)
                                            <option value="{{ $parent->id }}" {{ $menu->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Order:</label>
                                    <input type="text" class="form-control" name="order" value="{{$menu->order}}">
                                </div>
                                <div class="form-group">
                                    <label>Role:</label>
                                    <input type="text" class="form-control" name="role" value="{{$menu->role}}">
                                </div>

                                <div class="btn-group mt-2">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
