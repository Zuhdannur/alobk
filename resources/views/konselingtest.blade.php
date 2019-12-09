<html>
<head>
	<title>Rekap konseling</title>
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
		<h5>Rekap konseling</h4>
	</center>
 
	<table class='table table-bordered'>
		<thead>
			<tr>
				<td rowspan="2">No</td>
                <td colspan="2">Siswa</td>
				<td colspan="2">Guru BK</td>
				<td rowspan="2">Judul</td>
				<td rowspan="2">Deskripsi masalah</td>
                <td rowspan="2">Catatan Konseling</td>
                <td rowspan="2">Komentar siswa</td>
                <td rowspan="2">Rating siswa</td>
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
				<td>{{ $i++ }}</td>
				<td>{{$p->requester->username}}</td>
                <td>{{$p->requester->name}}</td>
                <td>{{$p->consultant->username}}</td>
				<td>{{$p->consultant->name}}</td>
				<td>{{$p->title}}</td>
				<td>{{$p->desc}}</td>
				<td>{{ $p->type_schedule == 'direct' ? 'Lokasi: '.$p->location."; Waktu: ".$p->time: 
					$data = Firebase::get('/room/messages/'.$p->id,['print'=> 'pretty'])
					$characters = json_decode($data, 1)  @foreach($characters as $key => $val) $val['message'];  }}</td>
				<td>{{ !empty($p->feedback) ? $p->feedback->komentar:'-' }}</td>
				<td>{{ !empty($p->feedback) ? $p->feedback->rating."/5":'-' }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
 
</body>
</html>
