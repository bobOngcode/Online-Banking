<?php

use Illuminate\Database\Seeder;
use App\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branches = [
            ['name' => 'ADMINISTRATION', 	'code' => 'ADMN'],
            ['name' => 'AGOO', 	'code' => 'AGOO'],
            ['name' => 'ALAMINOS', 	'code' => 'ALAM'],
            ['name' => 'APALIT', 	'code' => 'APLT'],
            ['name' => 'APPLETRONICS', 	'code' => 'APPL'],
            ['name' => 'BAGUIO', 	'code' => 'BAGU'],
            ['name' => 'BALANGA', 	'code' => 'BALA'],
            ['name' => 'BALIUAG', 	'code' => 'BALI'],
            ['name' => 'BAMBANG', 	'code' => 'BAMB'],
            ['name' => 'BANTAY', 	'code' => 'BANT'],
            ['name' => 'BATAC', 	'code' => 'BATC'],
            ['name' => 'BAYAMBANG', 	'code' => 'BAYA'],
            ['name' => 'BINALONAN', 	'code' => 'BINA'],
            ['name' => 'BUNTUN', 	'code' => 'BUNT'],
            ['name' => 'CAMILING', 	'code' => 'CAMI'],
            ['name' => 'CANDON', 	'code' => 'CAND'],
            ['name' => 'CAPAS', 	'code' => 'CAPA'],
            ['name' => 'CAUAYAN', 	'code' => 'CAUA'],
            ['name' => 'CONCEPCION', 	'code' => 'CONC'],
            ['name' => 'DAGUPAN', 	'code' => 'DAGU'],
            ['name' => 'GAPAN', 	'code' => 'GAPA'],
            ['name' => 'IBA', 	'code' => 'IBAZ'],
            ['name' => 'ILAGAN', 	'code' => 'ILAG'],
            ['name' => 'LAOAG', 	'code' => 'LAOA'],
            ['name' => 'LINGAYEN', 	'code' => 'LING'],
            ['name' => 'MABALACAT', 	'code' => 'MABA'],
            ['name' => 'MAGALANG', 	'code' => 'MAGA'],
            ['name' => 'MALOLOS', 	'code' => 'MALO'],
            ['name' => 'MANAOAG', 	'code' => 'MANA'],
            ['name' => 'MANGALDAN', 	'code' => 'MANG'],
            ['name' => 'MANGATAREM', 	'code' => 'MANM'],
            ['name' => 'MARIVELES', 	'code' => 'MARI'],
            ['name' => 'MONCADA', 	'code' => 'MONC'],
            ['name' => 'NAGUILIAN', 	'code' => 'NAGU'],
            ['name' => 'PANDAN', 	'code' => 'PAND'],
            ['name' => 'PANIQUI', 	'code' => 'PANI'],
            ['name' => 'POZORRUBIO', 	'code' => 'POZO'],
            ['name' => 'ROSALES', 	'code' => 'ROSS'],
            ['name' => 'ROSARIO', 	'code' => 'ROSO'],
            ['name' => 'ROXAS', 	'code' => 'ROXA'],
            ['name' => 'SAN CARLOS', 	'code' => 'SNCA'],
            ['name' => 'SAN FABIAN', 	'code' => 'SANF'],
            ['name' => 'SAN FERNANDO', 	'code' => 'SFLU'],
            ['name' => 'SAN JOSE', 	'code' => 'SNJO'],
            ['name' => 'SANCHEZ MIRA', 	'code' => 'SANC'],
            ['name' => 'SANTA CRUZ', 	'code' => 'STCR'],
            ['name' => 'SANTIAGO', 	'code' => 'STGO'],
            ['name' => 'SERVICE', 	'code' => 'SERV'],
            ['name' => 'SINDALAN', 	'code' => 'SIND'],
            ['name' => 'SOLANO', 	'code' => 'SOLA'],
            ['name' => 'STA. MARIA', 	'code' => 'STAM'],
            ['name' => 'SUBIC', 	'code' => 'SUBI'],
            ['name' => 'TARLAC', 	'code' => 'TARL'],
            ['name' => 'TAYUG', 	'code' => 'TAYU'],
            ['name' => 'TUGUEGARAO', 	'code' => 'TUGU'],
            ['name' => 'TUMAUINI', 	'code' => 'TUMA'],
            ['name' => 'UMINGAN', 	'code' => 'UMIN'],
            ['name' => 'URDA-ALEXANDER', 	'code' => 'URDA'],
            ['name' => 'URDA-NANCAYASAN', 	'code' => 'NANC'],
            ['name' => 'VILLASIS', 	'code' => 'VILL'],

        ];

        foreach ($branches as $branch) {
            Branch::create(['name' => $branch['name'], 'code' => $branch['code']]);
        }

    }
}
