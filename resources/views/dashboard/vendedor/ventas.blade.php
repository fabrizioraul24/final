@extends('layouts.sidebar-vendedor')

@section('title', 'Ventas | Vendedor')
@section('page-title', 'Ventas')

@section('content')
    {{-- Reutilizamos la vista de ventas adaptada al layout del vendedor --}}
    @include('dashboard.ventas', ['__vendor_embed' => true])
@endsection
