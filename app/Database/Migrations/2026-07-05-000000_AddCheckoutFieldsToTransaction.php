<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCheckoutFieldsToTransaction extends Migration
{
    public function up()
    {
        $fields = [
            'ppn' => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => null,
            ],
            'biaya_admin' => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => null,
            ],
            'voucher_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => null,
            ],
            'diskon_voucher' => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => null,
            ],
        ];

        $this->forge->addColumn('transaction', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', ['ppn', 'biaya_admin', 'voucher_code', 'diskon_voucher']);
    }
}
