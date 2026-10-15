@extends('layouts.app')
@section('title','Novo funcionário')
@section('header','Novo funcionário')
@section('subheader','Cadastro completo, acesso e remuneração')
@section('content')
<div class="max-w-5xl mx-auto">@include('manager.users._form')</div>
@endsection
