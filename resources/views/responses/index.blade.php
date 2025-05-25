@extends('layouts.app')

@section('title', 'Lihat Jawaban')

@section('content')
<div class="container mt-4">
    <h2>Lihat Jawaban User</h2>

    <!-- Filter berdasarkan Form -->
    <form method="GET" action="{{ route('responses.index') }}" class="mb-3">
        <div class="input-group">
            <select name="form_id" class="form-select">
                <option value="">Pilih Formulir</option>
                @foreach(App\Models\Form::all() as $form)
                    <option value="{{ $form->id }}" {{ request('form_id') == $form->id ? 'selected' : '' }}>
                        {{ $form->title }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>ID Formulir</th>
                <th>Judul Formulir</th>
                <th>Total Jawaban</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($responses as $response)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $response->form_id }}</td>
                <td>{{ $response->form->title }}</td>
                <td>{{ $response->answers->count() }}</td>
                <td>
                    <a href="{{ route('responses.show', $response->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
