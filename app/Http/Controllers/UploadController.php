<?php

namespace App\Http\Controllers;

use App\Models\Pop;
use App\Models\Olt;
use App\Models\Odc;
use App\Models\Odp;
use App\Models\OdpPort;
use App\Models\PopPhoto;
use App\Models\OltPhoto;
use App\Models\OdcPhoto;
use App\Models\OdpPhoto;
use App\Models\PortPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    private $tableMap = [
        'pop' => [
            'model' => Pop::class,
            'photo_model' => PopPhoto::class,
            'id_column' => 'pop_id',
            'upload_dir' => 'pop'
        ],
        'olt' => [
            'model' => Olt::class,
            'photo_model' => OltPhoto::class,
            'id_column' => 'olt_id',
            'upload_dir' => 'olt'
        ],
        'odc' => [
            'model' => Odc::class,
            'photo_model' => OdcPhoto::class,
            'id_column' => 'odc_id',
            'upload_dir' => 'odc'
        ],
        'odp' => [
            'model' => Odp::class,
            'photo_model' => OdpPhoto::class,
            'id_column' => 'odp_id',
            'upload_dir' => 'odp'
        ],
        'port' => [
            'model' => OdpPort::class,
            'photo_model' => PortPhoto::class,
            'id_column' => 'port_id',
            'upload_dir' => 'port'
        ]
    ];

    public function handle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        $method = $request->method();

        if ($method === 'POST' || $method === 'DELETE') {
            if (Auth::user()->role === 'viewer') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
            }
        }

        switch ($method) {
            case 'POST':
                return $this->uploadPhoto($request);
            case 'DELETE':
                return $this->deletePhoto($request);
            case 'GET':
                return $this->getPhotos($request);
            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function uploadPhoto(Request $request)
    {
        $type = $request->input('type');
        $deviceId = (int)$request->input('device_id');

        if (!isset($this->tableMap[$type])) {
            return response()->json(['error' => 'Tipe device tidak valid'], 400);
        }

        if (!$deviceId) {
            return response()->json(['error' => 'Device ID harus diisi'], 400);
        }

        $map = $this->tableMap[$type];
        $modelClass = $map['model'];
        $photoModelClass = $map['photo_model'];
        $idColumn = $map['id_column'];
        $uploadSubDir = $map['upload_dir'];

        // Cek apakah device exists
        $device = $modelClass::find($deviceId);
        if (!$device) {
            return response()->json(['error' => ucfirst($type) . ' tidak ditemukan'], 404);
        }

        // Cek jumlah foto existing
        $existingCount = $photoModelClass::where($idColumn, $deviceId)->count();

        $maxPhotos = 5;
        $files = $request->file('photos') ?? [];
        if (!is_array($files)) {
            $files = $request->hasFile('photos') ? [$files] : [];
        }
        $uploadCount = count($files);

        if ($existingCount + $uploadCount > $maxPhotos) {
            return response()->json(['error' => "Maksimal $maxPhotos foto. Saat ini sudah ada $existingCount foto."], 400);
        }

        if (empty($files)) {
            return response()->json(['error' => 'File foto harus diupload'], 400);
        }

        $uploadDir = public_path('uploads/' . $uploadSubDir);
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedPhotos = [];
        $errors = [];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                $errors[] = "Error upload file: " . $file->getClientOriginalName();
                continue;
            }

            // Validasi ekstensi
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = "Ekstensi file tidak diizinkan: " . $file->getClientOriginalName() . " (hanya " . implode(', ', $allowedExtensions) . ")";
                continue;
            }

            // Validasi mime
            $mimeType = $file->getMimeType();
            $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $errors[] = "Tipe file tidak diizinkan: " . $file->getClientOriginalName() . " (hanya JPG, PNG, GIF, WEBP)";
                continue;
            }

            // Validasi ukuran
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file->getSize() > $maxSize) {
                $errors[] = "File terlalu besar: " . $file->getClientOriginalName() . " (max 5MB)";
                continue;
            }

            // Generate filename
            $timestamp = time();
            $random = Str::random(16);
            $newFileName = $type . '_' . $deviceId . '_' . $timestamp . '_' . $random . '.' . $extension;

            try {
                $file->move($uploadDir, $newFileName);

                $isPrimary = ($existingCount === 0 && count($uploadedPhotos) === 0) ? true : false;

                $photo = $photoModelClass::create([
                    $idColumn => $deviceId,
                    'filename' => $newFileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'is_primary' => $isPrimary
                ]);

                $uploadedPhotos[] = [
                    'id' => $photo->id,
                    'filename' => $newFileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'is_primary' => $isPrimary,
                    'url' => 'uploads/' . $uploadSubDir . '/' . $newFileName,
                    'created_at' => now()->toDateTimeString()
                ];
            } catch (\Exception $e) {
                $errors[] = "Gagal menyimpan file: " . $file->getClientOriginalName() . " - " . $e->getMessage();
            }
        }

        // Update has_photo flag
        if (count($uploadedPhotos) > 0) {
            $device->update(['has_photo' => true]);
        }

        $response = [
            'success' => count($errors) === 0,
            'message' => count($uploadedPhotos) . ' foto berhasil diupload',
            'photos' => $uploadedPhotos,
            'total_photos' => $existingCount + count($uploadedPhotos)
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['success'] = false;
        }

        return response()->json($response);
    }

    protected function deletePhoto(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        $photoId = isset($data['photo_id']) ? (int)$data['photo_id'] : 0;
        $type = $data['type'] ?? '';

        if (!isset($this->tableMap[$type])) {
            return response()->json(['error' => 'Tipe device tidak valid'], 400);
        }

        if (!$photoId) {
            return response()->json(['error' => 'Photo ID harus diisi'], 400);
        }

        $map = $this->tableMap[$type];
        $modelClass = $map['model'];
        $photoModelClass = $map['photo_model'];
        $idColumn = $map['id_column'];
        $uploadDir = $map['upload_dir'];

        try {
            DB::beginTransaction();

            $photo = $photoModelClass::find($photoId);
            if (!$photo) {
                DB::rollBack();
                return response()->json(['error' => 'Foto tidak ditemukan'], 404);
            }

            $deviceId = $photo->$idColumn;

            // Hapus file fisik
            $filePath = public_path('uploads/' . $uploadDir . '/' . $photo->filename);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // Hapus database
            $photo->delete();

            // Cek sisa foto
            $remaining = $photoModelClass::where($idColumn, $deviceId)->count();

            // Update has_photo
            if ($remaining === 0) {
                $device = $modelClass::find($deviceId);
                if ($device) {
                    $device->update(['has_photo' => false]);
                }
            }

            // Set new primary if deleted was primary
            if ($photo->is_primary && $remaining > 0) {
                $nextPhoto = $photoModelClass::where($idColumn, $deviceId)->orderBy('id', 'asc')->first();
                if ($nextPhoto) {
                    $nextPhoto->update(['is_primary' => true]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Foto berhasil dihapus', 'remaining_photos' => $remaining]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function getPhotos(Request $request)
    {
        $type = $request->query('type');
        $deviceId = (int)$request->query('device_id');

        if (!in_array($type, ['odc', 'odp', 'port'])) {
            return response()->json(['error' => 'Tipe device tidak valid'], 400);
        }

        if (!$deviceId) {
            return response()->json(['error' => 'Device ID harus diisi'], 400);
        }

        $map = $this->tableMap[$type] ?? null;
        if (!$map) {
            return response()->json(['error' => 'Tipe device tidak valid'], 400);
        }

        $photoModelClass = $map['photo_model'];
        $idColumn = $map['id_column'];

        try {
            $photos = $photoModelClass::where($idColumn, $deviceId)
                ->orderBy('is_primary', 'desc')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($photo) use ($type) {
                    $photo->url = 'uploads/' . $type . '/' . $photo->filename;
                    return $photo;
                });

            return response()->json($photos);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
