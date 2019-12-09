<html>
<head>
	<title>Membuat Laporan PDF Dengan DOMPDF Laravel</title>
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
		<h5>Backup Diari</h4>
	</center>
 
	<table class='table table-bordered'>
		<thead>
			<tr>
				<th>No</th>
				<th>Nama</th>
				<th>User ID</th>
				<th>Judul diari</th>
				<th>Deskripsi diari</th>
			</tr>
		</thead>
		<tbody>
			@php $i=1 @endphp
			@foreach($diari as $p)
			<tr>
				<td>{{ $i++ }}</td>
				<td>{{$p->user->name}}</td>
				<td>{{$p->user_id}}</td>
				<td>{{$p->title}}</td>
				<td>{{$p->body}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
 
</body>
</html>
