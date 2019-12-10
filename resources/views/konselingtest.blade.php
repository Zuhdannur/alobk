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
		<h2>Rekap konseling</h2>
		<h3>{{ $time }}</h3>
		<h3>{{ $nama_sekolah }}</h3>
	</center>
 
	<table class='table table-bordered'>
		<thead>
			<tr>
				<th rowspan="2">No</th>
                <th colspan="2">Siswa</th>
				<th colspan="2">Guru BK</th>
				<th rowspan="2">Judul</th>
				<th rowspan="2">Deskripsi masalah</th>
                <th rowspan="2">Catatan Konseling</th>
                <th rowspan="2">Komentar siswa</th>
                <th rowspan="2">Rating siswa</th>
            </tr>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Nama</th>
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
                <td>
                    <?php $data = json_decode(Firebase::get('/room/messages/'.$p->id,['print'=> 'pretty']), 1); ?>
                    @if($p->type_schedule == 'direct')
                        {{'Lokasi: '.$p->location."; Waktu: ".$p->time}}
                    @else
                        @if(is_array($data) || is_object($data))
                            @foreach(($data) as $key => $val) 
                                {!! \App\User::find($val['senderId'])->name.": \"".$val["message"]. "\"" !!} <br/>
                            @endforeach
                        @endif
                    @endif    
                </td>
				<td>{{ !empty($p->feedback) ? $p->feedback->komentar:'-' }}</td>
				<td>{{ !empty($p->feedback) ? $p->feedback->rating."/5":'-' }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
 
</body>
</html>
