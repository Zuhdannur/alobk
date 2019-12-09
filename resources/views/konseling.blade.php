<html>
<head>
	<title>Rekap diari</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
	<style type="text/css">
		table tr td,
		table tr th{
			font-size: 9pt;
		}
	</style>
	<center>
		<h5>Rekap diari</h4>
	</center>
 
	<table class='table table-bordered'>
		<thead>
			<tr>
                <td rowspan="2">ID Konseling</td>
                <td colspan="2">Siswa</td>
                <td colspan="2">Guru BK</td>
                <td rowspan="2">Catatan Konseling</td>
                <td rowspan="2">Komentar</td>
                <td rowspan="2">Rating</td>
            </tr>
            <tr>
                <td>NIS</td>
                <td>Nama</td>
                <td>NIP</td>
                <td>Nama</td>
            </tr>
		</thead>
		<tbody>
			@php $i=1 @endphp
			@foreach($konseling as $p)
			<tr>
				<td>{{$p->id}}</td>
				{{-- <td>{{$p->requester->username}}</td> --}}
				<td>Test</td>
				<td>Test</td>
				<td>Test</td>
				<td>Test</td>
                {{-- <td>{{$p->requester->name}}</td> --}}
                {{-- <td>{{$p->consultant->username}}</td> --}}
				{{-- <td>{{$p->consultant->name}}</td> --}}
                <td>test</td>
                <td>test</td>
			</tr>
			@endforeach
		</tbody>
	</table>
 
</body>
</html>
