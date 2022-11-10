@extends('admin.layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @if (Session::has('message'))
                    <div class="alert bg-success alert-success text-white text-center" role="alert">
                        {{ Session::get('message') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        Total estudiantes calificados: {{ $bookings->count() }}
                    </div>
                    <div class="card-body table-responsive-lg">
                        <table class="table table-striped">
                        <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Estudiante</th>
                                    <th scope="col">Correo</th>
                                    <th scope="col">Telefono</th>
                                    
                                    
                                    <th scope="col">Tutor</th>
                                    <th scope="col">Calificar</th>
                                </tr>
                            </thead>
                     
                            <tbody>
                                @forelse($bookings as $key=>$booking)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><img src="profile/{{ $booking->user->image }}" width="80">
                                        </td>
                                        <td>{{ $booking->date }}</td>
                                        <td>{{ $booking->user->name }}</td>
                                        <td>{{ $booking->user->email }}</td>
                                        <td>{{ $booking->user->phone_number }}</td>
                                       
                                        <td>{{ $booking->doctor->name }}</td>

                                        <td>
                                            @if (!App\Prescription::where('date', date('m-d-Y'))
                ->where('doctor_id', auth()->user()->id)
                ->where('user_id', $booking->user->id)
                ->exists())
                                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#exampleModal{{ $booking->user_id }}">
                                                    Prescribe
                                                </button>
                                                @include('prescription.form')

                                            @else
                                                <a href="{{ route('prescription.show', [$booking->user_id, $booking->date]) }}"
                                                    class="btn btn-info">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <td>No hay Calificaciones!</td>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL FORM --}}
    @include('prescription.form')

@endsection
