@extends('layouts.app')
@section('title')
    Articles 
@endsection
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Articles</h1>
            <div class="section-header-breadcrumb">
                <a href="{{ route('articles.create')}}" class="btn btn-primary form-btn">Article <i class="fas fa-plus"></i></a>
            </div>
        </div>
    <div class="section-body">
       <div class="card">
            <div class="card-body">
                @include('articles.table')
            </div>
       </div>
   </div>
    
    </section>
@endsection

