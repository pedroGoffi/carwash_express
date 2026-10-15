@extends('layouts.app')
@section('title','Editar funcionário')
@section('header','Editar funcionário')
@section('subheader','Informações, contato, remuneração e acesso')
@section('content')
<div class="max-w-5xl mx-auto"><div class="flex justify-end mb-4"><a class="cw-btn-secondary" href="{{ route('manager.payroll.index',['user_id'=>$user->id]) }}"><i class="fas fa-chart-line mr-2"></i>Custos deste funcionário</a></div>@include('manager.users._form')</div>
@endsection
