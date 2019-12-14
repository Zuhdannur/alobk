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
	<div class="row mt-2 mb-3">
			<center>
				<p class="h3">Rekap Diari</p>
				<p class="h6 text-muted">{{ $time }}</p>
				<p class="h6 text-muted">{{ $nama_sekolah }}</p>
			</center>
	</div>
	<table class='table table-bordered'>
		<thead>
			<tr>
				<th>No</th>
				<th>NIS</th>
				<th>Nama</th>
				<th>Judul</th>
				<th>Deskripsi</th>
				<th>Tanggal</th>
				<th>Dibuat</th>
			</tr>
		</thead>
		<tbody>
			@php $i=1 @endphp
			@foreach($diari as $p)
			<tr>
				<td>{{ $i++ }}</td>
				<td>{{$p->user_id}}</td>
				<td>{{$p->user->name}}</td>
				<td>{{$p->title}}</td>
				<td>{{$p->body}}</td>
				<td>{{$p->tgl}}</td>
				<td>{{ $p->dateCreatedAt() }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
