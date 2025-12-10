<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LHPP - {{ $lhpp->notification_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        .header { text-align: center; font-size: 14px; font-weight: bold; }
        .logo { width: 70px; height: auto; }
        .section-title { font-weight: bold; background-color: #f2f2f2; padding: 5px; }
        .signature-box { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>

    <!-- Bagian Header -->
    <div style="text-align: center; margin-bottom: 20px;">
        <table width="100%" style="border: none;">
            <tr>
                <td width="70%" style="text-align: center; border: none;">
                    <h2>
                        <strong>Laporan Hasil Penyelesaian Pekerjaan (LHPP)</strong><br>
                        JASA PEKERJAAN FABRIKASI, KONSTRUKSI & MESIN
                    </h2>
                </td>
            </tr>
        </table>
    </div>

    <!-- Bagian Informasi -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px;">
        <tbody>
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2; padding: 5px; border: 1px solid black;">ORDER</td>
                <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->notification_number }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2; padding: 5px; border: 1px solid black;">DESCRIPTION</td>
                <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->description_notifikasi }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2; padding: 5px; border: 1px solid black;">PURCHASING ORDER</td>
                <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->purchase_order_number }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2; padding: 5px; border: 1px solid black;">UNIT KERJA PEMINTA (USER)</td>
                <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->unit_kerja }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2; padding: 5px; border: 1px solid black;">TANGGAL SELESAI PEKERJAAN</td>
                <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->tanggal_selesai }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background-color: #f2f2f2; padding: 5px; border: 1px solid black;">WAKTU PENGERJAAN</td>
                <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->waktu_pengerjaan }} Hari</td>
            </tr>
        </tbody>
    </table>

    <!-- Bagian Tabel Material -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px;">
        <thead>
            <tr>
                <th colspan="5" style="background-color: #f6e7b0; padding: 8px; text-align: left; border: 1px solid black;">
                    NO. A. ACTUAL PEMAKAIAN MATERIAL
                </th>
            </tr>
            <tr style="background-color: #f6e7b0;">
                <th style="padding: 5px; border: 1px solid black;">No</th>
                <th style="padding: 5px; border: 1px solid black;">Material Description</th>
                <th style="padding: 5px; border: 1px solid black;">Volume (Kg)</th>
                <th style="padding: 5px; border: 1px solid black;">Harga Satuan (Rp)</th>
                <th style="padding: 5px; border: 1px solid black;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMaterial = 0; @endphp
            @foreach($lhpp->material_description ?? [] as $key => $desc)
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">{{ $key + 1 }}</td>
                    <td style="padding: 5px; border: 1px solid black;">{{ $desc }}</td>
                    <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->material_volume[$key] ?? 0 }}</td>
                    @php
                        $materialHargaSatuan = (float) ($lhpp->material_harga_satuan[$key] ?? 0);
                        $materialJumlah      = (float) ($lhpp->material_jumlah[$key] ?? 0);
                    @endphp

                    <td style="padding: 5px; border: 1px solid black;">{{ number_format($materialHargaSatuan, 0, ',', '.') }}</td>
                    <td style="padding: 5px; border: 1px solid black;">{{ number_format($materialJumlah, 0, ',', '.') }}</td>
                </tr>
                @php $totalMaterial += $materialJumlah; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f6e7b0;">
                <td colspan="3" style="padding: 5px; text-align: right; font-weight: bold; border: 1px solid black;">SUB TOTAL ( A )</td>
                <td colspan="2" style="padding: 5px; border: 1px solid black;">Rp {{ number_format($totalMaterial, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian Tabel Biaya Upah Kerja -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px;">
        <thead>
            <tr>
                <th colspan="5" style="background-color: #f6e7b0; padding: 8px; text-align: left; border: 1px solid black;">
                    NO. B. ACTUAL BIAYA JASA
                </th>
            </tr>
            <tr style="background-color: #f6e7b0;">
                <th style="padding: 5px; border: 1px solid black;">No</th>
                <th style="padding: 5px; border: 1px solid black;">Biaya Jasa Description</th>
                <th style="padding: 5px; border: 1px solid black;">Jumlah (Jam/Kg)</th>
                <th style="padding: 5px; border: 1px solid black;">Harga Satuan (Rp)</th>
                <th style="padding: 5px; border: 1px solid black;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalUpah = 0; @endphp
            @foreach($lhpp->upah_description ?? [] as $key => $desc)
                <tr>
                    <td style="padding: 5px; border: 1px solid black;">{{ $key + 1 }}</td>
                    <td style="padding: 5px; border: 1px solid black;">{{ $desc }}</td>
                    <td style="padding: 5px; border: 1px solid black;">{{ $lhpp->upah_volume[$key] ?? 0 }}</td>
                    @php
                        $upahHargaSatuan = (float) ($lhpp->upah_harga_satuan[$key] ?? 0);
                        $upahJumlah      = (float) ($lhpp->upah_jumlah[$key] ?? 0);
                    @endphp

                    <td style="padding: 5px; border: 1px solid black;">{{ number_format($upahHargaSatuan, 0, ',', '.') }}</td>
                    <td style="padding: 5px; border: 1px solid black;">{{ number_format($upahJumlah, 0, ',', '.') }}</td>
                </tr>
                @php $totalUpah += $upahJumlah; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f6e7b0;">
                <td colspan="3" style="padding: 5px; text-align: right; font-weight: bold; border: 1px solid black;">
                    SUB TOTAL ( B )
                </td>
                <td colspan="2" style="padding: 5px; border: 1px solid black;">
                    Rp {{ number_format($totalUpah, 0, ',', '.') }}
                </td>
            </tr>
            <tr style="background-color: #f6e7b0;">
                <td colspan="3" style="padding: 5px; text-align: right; font-weight: bold; border: 1px solid black;">
                    TOTAL ACTUAL BIAYA ( A + B  )
                </td>
                @php
                    $totalBiaya = (float) $totalMaterial + (float) $totalUpah;
                @endphp
                <td colspan="2" style="padding: 5px; border: 1px solid black;">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tabel Hasil Quality Control dan Unit -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px;">
        <thead>
            <tr style="background-color: #f6e7b0;">
                <th style="border: 1px solid black; padding: 8px;">HASIL QUALITY CONTROL</th>
                <th style="border: 1px solid black; padding: 8px;">UNIT KERJA PEMINTA</th>
                <th style="border: 1px solid black; padding: 8px;">UNIT WORKSHOP</th>
                <th style="border: 1px solid black; padding: 8px;">PT. PKM</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Kolom Hasil Quality Control -->
                <td style="border: 1px solid black; padding: 8px; text-align: left;">
                    <div style="margin-bottom: 5px;">
                        <span>APPROVE</span>
                        <input type="checkbox"
                               @if($lhpp->status_approve == 'Approved') checked @endif
                               disabled
                               style="width: 16px; height: 16px; margin-left: 10px; vertical-align: middle;">
                    </div>

                    <hr style="border-top: 1px solid black; margin: 5px 0;">

                    <div style="margin-top: 5px;">
                        <span>REJECT</span>
                        <input type="checkbox"
                               @if($lhpp->status_approve == 'Rejected') checked @endif
                               disabled
                               style="width: 16px; height: 16px; margin-left: 10px; vertical-align: middle;">
                    </div>
                </td>

                <!-- Kolom Tanda Tangan Manager User -->
                <td style="border: 1px solid black; text-align: center; padding: 8px;">
                    @php
                        $signaturePathRequesting = public_path("storage/signatures/lhpp/manager_signature_requesting_{$lhpp->notification_number}.png");
                        $userReq = $lhpp->manager_signature_requesting_user_id
                            ? \App\Models\User::find($lhpp->manager_signature_requesting_user_id)
                            : null;
                    @endphp

                    @if(!empty($lhpp->manager_signature_requesting) && file_exists($signaturePathRequesting))
                        <img src="{{ $signaturePathRequesting }}"
                             style="width: 80px; height: auto; margin-bottom: 5px;">
                    @else
                        <span>Manager User</span>
                    @endif
                    <br>
                    <strong>{{ $userReq->name ?? 'Manager User' }}</strong>
                </td>

                <!-- Kolom Tanda Tangan Manager Workshop -->
                <td style="border: 1px solid black; text-align: center; padding: 8px;">
                    @php
                        $signaturePathWorkshop = public_path("storage/signatures/lhpp/manager_signature_{$lhpp->notification_number}.png");
                        $userWs = $lhpp->manager_signature_user_id
                            ? \App\Models\User::find($lhpp->manager_signature_user_id)
                            : null;
                    @endphp

                    @if(!empty($lhpp->manager_signature) && file_exists($signaturePathWorkshop))
                        <img src="{{ $signaturePathWorkshop }}"
                             style="width: 80px; height: auto; margin-bottom: 5px;">
                    @else
                        <span>Herwanto S</span>
                    @endif
                    <br>
                    <strong>{{ $userWs->name ?? 'Herwanto S' }}</strong>
                </td>

                <!-- Kolom Tanda Tangan Manager PKM -->
                <td style="border: 1px solid black; text-align: center; padding: 8px;">
                    @php
                        $signaturePathPKM = public_path("storage/signatures/lhpp/manager_pkm_signature_{$lhpp->notification_number}.png");
                        $userPkm = $lhpp->manager_pkm_signature_user_id
                            ? \App\Models\User::find($lhpp->manager_pkm_signature_user_id)
                            : null;
                    @endphp

                    @if(!empty($lhpp->manager_pkm_signature) && file_exists($signaturePathPKM))
                        <img src="{{ $signaturePathPKM }}"
                             style="width: 80px; height: auto; margin-bottom: 5px;">
                    @else
                        <span>MANAGER PT. Prima Karya Manunggal</span>
                    @endif
                    <br>
                    <strong>{{ $userPkm->name ?? 'MANAGER PKM' }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Bagian Catatan dan Tindakan -->
    @php
        // Model sudah cast -> array, tapi buat jaga2 kalau masih string/null
        $requestingNotes = is_array($lhpp->requesting_notes)
            ? $lhpp->requesting_notes
            : (empty($lhpp->requesting_notes) ? [] : (json_decode($lhpp->requesting_notes, true) ?: []));

        $controllingNotes = is_array($lhpp->controlling_notes)
            ? $lhpp->controlling_notes
            : (empty($lhpp->controlling_notes) ? [] : (json_decode($lhpp->controlling_notes, true) ?: []));
    @endphp

    <div style="margin-top: 20px; border: 1px solid black; padding: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="border: 1px solid black; padding: 8px; text-align: left; width: 50%;">Catatan User</th>
                    <th style="border: 1px solid black; padding: 8px; text-align: left; width: 50%;">Catatan Unit Workshop</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Catatan User -->
                    <td style="border: 1px solid black; padding: 8px; vertical-align: top;">
                        @if(!empty($requestingNotes))
                            @foreach($requestingNotes as $note)
                                <p><strong>{{ $loop->iteration }}.</strong> {{ $note['note'] ?? '' }}</p>
                                @php
                                    $user = !empty($note['user_id']) ? \App\Models\User::find($note['user_id']) : null;
                                @endphp
                                <small><em>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</em></small>
                                <br>
                            @endforeach
                        @else
                            <p>-</p>
                        @endif
                    </td>

                    <!-- Catatan Unit Workshop -->
                    <td style="border: 1px solid black; padding: 8px; vertical-align: top;">
                        @if(!empty($controllingNotes))
                            @foreach($controllingNotes as $note)
                                <p><strong>{{ $loop->iteration }}.</strong> {{ $note['note'] ?? '' }}</p>
                                @php
                                    $user = !empty($note['user_id']) ? \App\Models\User::find($note['user_id']) : null;
                                @endphp
                                <small><em>Ditambahkan oleh: {{ $user ? $user->jabatan : 'Pengguna Tidak Dikenal' }}</em></small>
                                <br>
                            @endforeach
                        @else
                            <p>-</p>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Dokumentasi Pekerjaan -->
    <div style="page-break-before: always; padding-top: 20px; text-align: center;">
        <h3 style="font-weight: bold; font-size: 16px; color: black; margin-bottom: 15px;">
            Dokumentasi Pekerjaan Selesai
        </h3>

        @php
            // Pastikan $lhpp->images adalah array
            $imagesRaw = $lhpp->images;
            $images = is_array($imagesRaw)
                ? $imagesRaw
                : (empty($imagesRaw) ? [] : (json_decode($imagesRaw, true) ?: []));
            $columns = 3; // Jumlah gambar per baris
        @endphp

        @if(is_array($images) && count($images) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    @foreach(array_chunk($images, $columns) as $chunk)
                        <tr>
                            @foreach($chunk as $image)
                                <td style="border: 1px solid black; padding: 10px; text-align: center;">
                                    @php
                                        $imgPath = !empty($image['path'])
                                            ? public_path('storage/' . $image['path'])
                                            : null;
                                    @endphp

                                    @if($imgPath && file_exists($imgPath))
                                        <img src="{{ $imgPath }}"
                                             alt="Dokumentasi LHPP"
                                             style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px;">
                                    @else
                                        <div style="width:150px;height:150px;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:10px;color:#999;">
                                            Gambar tidak ditemukan
                                        </div>
                                    @endif

                                    <p style="font-size: 12px; color: #555; margin-top: 5px;">
                                        {{ $image['description'] ?? 'Tanpa Keterangan' }}
                                    </p>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="text-align: center; font-style: italic; color: gray;">Tidak ada dokumentasi pekerjaan.</p>
        @endif
    </div>

</body>
</html>
