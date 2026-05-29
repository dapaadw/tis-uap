<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Container;
use App\Models\TrackingLog;
use OpenApi\Attributes as OA;

class ContainerController extends Controller
{
    #[OA\Get(
        path: "/api/v1/gateway/containers",
        summary: "Ambil Data Kontainer via Gateway",
        security: [["bearerAuth" => []]],
        tags: ["Containers"],
        responses: [
            new OA\Response(response: 200, description: "Sukses")
        ]
    )]
    public function index()
    {
        $containers = Container::with('trackingLogs')->get();
        return response()->json($containers, 200);
    }

    public function search(Request $request)
    {
        $query = Container::with('trackingLogs');

        if ($request->has('type')) {
            $query->where('waste_type', $request->type);
        }
        if ($request->has('min_weight')) {
            $query->where('weight_kg', '>=', $request->min_weight);
        }

        return response()->json($query->get(), 200);
    }

    #[OA\Post(
        path: "/api/v1/gateway/containers",
        summary: "Tambah Kontainer Baru via Gateway",
        security: [["bearerAuth" => []]],
        tags: ["Containers"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "container_id", type: "string", example: "GD12345"),
                    new OA\Property(property: "waste_type", type: "string", example: "Plastic"),
                    new OA\Property(property: "weight_kg", type: "number", example: 500)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Kontainer berhasil disimpan"),
            new OA\Response(response: 403, description: "Forbidden (Bukan Admin)"),
            new OA\Response(response: 422, description: "Validasi Gagal")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'container_id' => ['required', 'regex:/^[A-Za-z]{2}\d{5}$/', 'unique:containers,container_id'],
            'waste_type' => 'required|string',
            'weight_kg' => 'required|numeric|min:10|max:5000',
        ]);

        if (strtolower($request->waste_type) === 'chemical' && $request->weight_kg > 1000) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => ['weight_kg' => ['Berat Chemical tidak boleh lebih dari 1000 kg']]
            ], 422);
        }

        $container = Container::create([
            'container_id' => strtoupper($request->container_id),
            'waste_type' => $request->waste_type,
            'weight_kg' => $request->weight_kg,
            'status' => 'Active'
        ]);

        $kodeGudang = strtoupper(substr($request->container_id, 0, 2));
        
        TrackingLog::create([
            'container_id' => $container->container_id,
            'location' => 'Gudang ' . $kodeGudang,
            'description' => 'Kontainer didaftarkan.'
        ]);

        return response()->json(['message' => 'Kontainer berhasil disimpan', 'data' => $container], 201);
    }

    public function archive($id)
    {
        $container = Container::where('container_id', $id)->first();
        if (!$container) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $container->update(['status' => 'Archived']);

        $lastLog = TrackingLog::where('container_id', $id)->latest()->first();
        $lokasiAkhir = $lastLog ? $lastLog->location : 'Lokasi Tidak Diketahui';

        TrackingLog::create([
            'container_id' => $container->container_id,
            'location' => $lokasiAkhir,
            'description' => 'Status kontainer diarsipkan (Archived).'
        ]);

        return response()->json(['message' => 'Status diubah ke Archived'], 200);
    }

    public function destroy($id)
    {
        $container = Container::where('container_id', $id)->first();
        if (!$container) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $container->delete();
        return response()->json(['message' => 'Kontainer dihapus'], 200);
    }

    public function logs($id)
    {
        $container = Container::with('trackingLogs')->where('container_id', $id)->first();
        if (!$container) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json($container->trackingLogs, 200);
    }
}