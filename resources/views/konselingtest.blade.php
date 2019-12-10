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
						<th rowspan="2" class="align-middle">No</th>
						<th colspan="2" class="text-center">Siswa</th>
						<th colspan="2" class="text-center">Guru BK</th>
						<th colspan="5" class="text-center">Konseling</th>
					</tr>
					<tr>
						<th class="text-center align-middle">NIS</th>
						<th class="text-center align-middle">Nama</th>
						<th class="text-center align-middle">NIP</th>
						<th class="text-center align-middle">Nama</th>
						<th rowspan="2" class="align-middle">Judul</th>
						<th rowspan="2" class="align-middle">Deskripsi masalah</th>
						<th rowspan="2" class="align-middle">Catatan Konseling</th>
						<th rowspan="2" class="text-center align-middle">Komentar siswa</th>
						<th rowspan="2" class="text-center align-middle">Rating siswa</th>
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
