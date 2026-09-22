<?php

namespace Tests\Feature\Master;

use App\Models\Species;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_species_with_asfis_and_local_data()
    {
        $species = Species::create([
            'fao_code' => 'SKJ',
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang',
            'is_indonesia' => true,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('species', [
            'fao_code' => 'SKJ',
            'local_name_id' => 'Cakalang',
        ]);

        $this->assertTrue($species->is_indonesia);
    }

    public function test_scopes_work_correctly()
    {
        Species::create(['fao_code' => 'AAA', 'is_active' => true, 'is_aceh' => true, 'is_indonesia' => false]);
        Species::create(['fao_code' => 'BBB', 'is_active' => false, 'is_aceh' => false, 'is_indonesia' => true]);

        $this->assertEquals(1, Species::active()->count());
        $this->assertEquals(1, Species::aceh()->count());
        $this->assertEquals(1, Species::indonesia()->count());
    }

    public function test_fao_code_must_be_unique()
    {
        Species::create(['fao_code' => 'SKJ']);

        $this->expectException(QueryException::class);

        Species::create(['fao_code' => 'SKJ']);
    }
}
