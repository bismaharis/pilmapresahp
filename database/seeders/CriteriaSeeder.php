<?php
// database/seeders/CriteriaSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. KRITERIA UTAMA (Level 0)
        // CU: 35%, GK: 35%, BI: 30%
        $id_cu = DB::table('criterias')->insertGetId([
            'name' => 'Capaian Unggulan',
            'type' => 'cu',
            'weight' => 0.35,
            'max_score' => 0, 
            'parent_id' => null,
            'created_at' => now(), 'updated_at' => now()
        ]);

        $id_gk = DB::table('criterias')->insertGetId([
            'name' => 'Gagasan Kreatif',
            'type' => 'gk',
            'weight' => 0.35,
            'max_score' => 0,
            'parent_id' => null,
            'created_at' => now(), 'updated_at' => now()
        ]);

        $id_bi = DB::table('criterias')->insertGetId([
            'name' => 'Bahasa Inggris',
            'type' => 'bi',
            'weight' => 0.30,
            'max_score' => 0,
            'parent_id' => null,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. SUB-KRITERIA: CAPAIAN UNGGULAN (CU)
        // Semua bobot lokal 0.143 (rata). Max Score 50 per item (Default)
        $cu_items = [
            'Kompetisi', 'Pengakuan', 'Penghargaan', 'Karir Organisasi',
            'Hasil Karya', 'Pemberdayaan / Aksi Kemanusiaan', 'Kewirausahaan'
        ];

        foreach ($cu_items as $item) {
            DB::table('criterias')->insert([
                'name' => $item,
                'type' => 'cu',
                'weight' => 1/7, 
                'max_score' => 50,
                'parent_id' => $id_cu,
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 3. SUB-KRITERIA: BAHASA INGGRIS (BI)
        $bi_items = [
            ['name' => 'Content', 'weight' => 0.25, 'max' => 25],
            ['name' => 'Accuracy', 'weight' => 0.25, 'max' => 25],
            ['name' => 'Fluency', 'weight' => 0.20, 'max' => 20],
            ['name' => 'Pronunciation', 'weight' => 0.20, 'max' => 20],
            ['name' => 'Overall Performance', 'weight' => 0.10, 'max' => 10],
        ];

        foreach ($bi_items as $item) {
            DB::table('criterias')->insert([
                'name' => $item['name'],
                'type' => 'bi',
                'weight' => $item['weight'],
                'max_score' => $item['max'],
                'parent_id' => $id_bi,
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 4. SUB-KRITERIA: GAGASAN KREATIF (GK) 
        
        // Naskah Gagasan Kreatif (50% dari Total GK)
        $id_gk_naskah = DB::table('criterias')->insertGetId([
            'name' => 'Naskah Gagasan Kreatif',
            'type' => 'gk',
            'weight' => 0.50, 
            'max_score' => 0,
            'parent_id' => $id_gk,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Presentasi Gagasan Kreatif (50% dari Total GK)
        $id_gk_presentasi = DB::table('criterias')->insertGetId([
            'name' => 'Presentasi Gagasan Kreatif',
            'type' => 'gk',
            'weight' => 0.50,
            'max_score' => 0,
            'parent_id' => $id_gk,
            'created_at' => now(), 'updated_at' => now()
        ]);
        
        // Naskah: Penyajian (10%)
        $id_gk_naskah_penyajian = DB::table('criterias')->insertGetId([
            'name' => 'Penyajian', 'type' => 'gk', 'weight' => 0.10, 'max_score' => 0, 'parent_id' => $id_gk_naskah, 'created_at' => now(), 'updated_at' => now()
        ]);
            DB::table('criterias')->insert([
                ['name' => 'Bahasa Indonesia', 'type' => 'gk', 'weight' => 0.50, 'max_score' => 5, 'parent_id' => $id_gk_naskah_penyajian, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pengutipan', 'type' => 'gk', 'weight' => 0.50, 'max_score' => 5, 'parent_id' => $id_gk_naskah_penyajian, 'created_at' => now(), 'updated_at' => now()],
            ]);

        // Naskah: Substansi (70%)
        $id_gk_naskah_substansi = DB::table('criterias')->insertGetId([
            'name' => 'Substansi', 'type' => 'gk', 'weight' => 0.70, 'max_score' => 0, 'parent_id' => $id_gk_naskah, 'created_at' => now(), 'updated_at' => now()
        ]);
            $substansi_items = [
                ['name' => 'Fakta / Gejala', 'weight' => 8 / 70, 'max' => 8],
                ['name' => 'Identifikasi Masalah', 'weight' => 8 / 70, 'max' => 8],
                ['name' => 'Rumusan Masalah', 'weight' => 10 / 70, 'max' => 10],
                ['name' => 'Akibat', 'weight' => 8 / 70, 'max' => 8],
                ['name' => 'Solusi SMART', 'weight' => 15 / 70, 'max' => 15], 
                ['name' => 'Dampak Lanjutan', 'weight' => 8 / 70, 'max' => 8],
                ['name' => 'Langkah Tindakan', 'weight' => 8 / 70, 'max' => 8],
                ['name' => 'Kendala', 'weight' => 5 / 70, 'max' => 5],
            ];
            foreach($substansi_items as $item) {
                DB::table('criterias')->insert(['name' => $item['name'], 'type' => 'gk', 'weight' => $item['weight'], 'max_score' => $item['max'], 'parent_id' => $id_gk_naskah_substansi, 'created_at' => now(), 'updated_at' => now()]);
            }

        // Naskah: Kualitas (20%)
        $id_gk_naskah_kualitas = DB::table('criterias')->insertGetId([
            'name' => 'Kualitas', 'type' => 'gk', 'weight' => 0.20, 'max_score' => 0, 'parent_id' => $id_gk_naskah, 'created_at' => now(), 'updated_at' => now()
        ]);
            DB::table('criterias')->insert([
                ['name' => 'Keunikan', 'type' => 'gk', 'weight' => 0.50, 'max_score' => 10, 'parent_id' => $id_gk_naskah_kualitas, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Keterlaksanaan', 'type' => 'gk', 'weight' => 0.50, 'max_score' => 10, 'parent_id' => $id_gk_naskah_kualitas, 'created_at' => now(), 'updated_at' => now()],
            ]);

        // Presentasi: Penyajian (50%)
        $id_gk_pres_delivery = DB::table('criterias')->insertGetId([
            'name' => 'Presentasi (Delivery)', 'type' => 'gk', 'weight' => 0.50, 'max_score' => 0, 'parent_id' => $id_gk_presentasi, 'created_at' => now(), 'updated_at' => now()
        ]);
             DB::table('criterias')->insert([
                ['name' => 'Poster', 'type' => 'gk', 'weight' => 15/50, 'max_score' => 15, 'parent_id' => $id_gk_pres_delivery, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Sistematika', 'type' => 'gk', 'weight' => 15/50, 'max_score' => 15, 'parent_id' => $id_gk_pres_delivery, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cara Menjelaskan', 'type' => 'gk', 'weight' => 15/50, 'max_score' => 15, 'parent_id' => $id_gk_pres_delivery, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Ketepatan Waktu', 'type' => 'gk', 'weight' => 5/50, 'max_score' => 5, 'parent_id' => $id_gk_pres_delivery, 'created_at' => now(), 'updated_at' => now()],
            ]);

        // Presentasi: Tanya Jawab (50%)
        $id_gk_pres_qa = DB::table('criterias')->insertGetId([
            'name' => 'Tanya Jawab', 'type' => 'gk', 'weight' => 0.50, 'max_score' => 0, 'parent_id' => $id_gk_presentasi, 'created_at' => now(), 'updated_at' => now()
        ]);
            DB::table('criterias')->insert([
                ['name' => 'Ketepatan Jawaban', 'type' => 'gk', 'weight' => 30/50, 'max_score' => 30, 'parent_id' => $id_gk_pres_qa, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Cara Menjawab', 'type' => 'gk', 'weight' => 20/50, 'max_score' => 20, 'parent_id' => $id_gk_pres_qa, 'created_at' => now(), 'updated_at' => now()],
            ]);
    }
}