<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\RajaOngkirService;

class TransaksiController extends BaseController
{   
    protected $cart;

    public function __construct()
    {
        helper(['number', 'form']);
        $this->cart = service('cart');
    }
    public function index()
    {  
        $data = [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ];

        return view('v_keranjang', $data);
    }

    public function cart_add()
    {
        $this->cart->insert([
            'id'      => $this->request->getPost('id'),
            'qty'     => 1,
            'price'   => $this->request->getPost('harga'),
            'name'    => $this->request->getPost('nama'),
            'options' => [
                'foto' => $this->request->getPost('foto')
            ]
        ]);
        
        session()->setFlashdata(
            'success',
            'Produk berhasil ditambahkan ke keranjang. 
            <a href="' . base_url('keranjang') . '">Lihat</a>'
        );
        
        return redirect()->to(base_url('/'));
    } 
    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $item) {
            $qty = $this->request->getPost('qty' . $i++);

            $this->cart->update([
                'rowid' => $item['rowid'],
                'qty'   => $qty
            ]);
        }

        session()->setFlashdata(
            'success',
            'Keranjang berhasil diperbarui'
        );

        return redirect()->to(base_url('keranjang'));
    }
    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);

        session()->setFlashdata(
            'success',
            'Produk berhasil dihapus dari keranjang'
        );

        return redirect()->to(base_url('keranjang'));
    }
    public function cart_clear()
    {
        $this->cart->destroy();

        session()->setFlashdata(
            'success',
            'Keranjang berhasil dikosongkan'
        );

        return redirect()->to(base_url('keranjang'));
    }
    public function checkout()
    {  
        
        $data = [
            'items' => $this->cart->contents(),
            'total' => $this->cart->total()
        ];

        return view('v_checkout', $data);
    }
    public function destinations()
    {
        $search = $this->request->getGet('q');

        $service = new RajaOngkirService();
        $response = $service->getDestination($search);

        $data = [];

        if (is_array($response)) {
            if (!empty($response['data']) && is_array($response['data'])) {
                $data = $response['data'];
            } elseif (!empty($response['results']) && is_array($response['results'])) {
                $data = $response['results'];
            } elseif (!empty($response['rajaongkir']['results']) && is_array($response['rajaongkir']['results'])) {
                $data = $response['rajaongkir']['results'];
            } elseif (!empty($response['rajaongkir']['data']) && is_array($response['rajaongkir']['data'])) {
                $data = $response['rajaongkir']['data'];
            }
        }

        $results = [];

        foreach ($data as $item) {
            $id = $item['id'] ?? ($item['destination_id'] ?? ($item['city_id'] ?? ($item['subdistrict_id'] ?? null)));
            $text = $item['label'] ?? $item['name'] ?? null;

            if ($text === null) {
                $parts = [];
                foreach (['subdistrict', 'city', 'province', 'postal_code'] as $key) {
                    if (!empty($item[$key])) {
                        $parts[] = $item[$key];
                    }
                }
                $text = implode(', ', $parts);
            }

            if ($id !== null && $text !== null) {
                $results[] = [
                    'id'   => $id,
                    'text' => $text
                ];
            }
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }

    public function costs()
    {
        $origin = '64999';
        $destination = $this->request->getGet('destination');
        $weight = '1000';
        $courier = 'jne'; 

        $service = new RajaOngkirService();
        $response = $service->getCost($origin, $destination, $weight, $courier);

        $results = [];
        $data = $response['rajaongkir']['results'][0]['costs'] ?? [];

        foreach ($data as $item) {
            $results[] = [
                'service'     => $item['service'],
                'description' => $item['description'],
                'cost'        => $item['cost'],
                'etd'         => $item['etd']
            ];
        }

        return $this->response->setJSON($results);
    }

    public function buy()
    { 
        $cartItems = $this->cart->contents();

        if (empty($cartItems)) {
            return redirect()->back();
        }

        $db = \Config\Database::connect();
        $db->transStart(); 

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }

        $ongkir = (int) $this->request->getPost('ongkir');

        $transaction = [
            'username'    => $this->request->getPost('username'),
            'alamat'      => $this->request->getPost('alamat'),
            'ongkir'      => $ongkir,
            'total_harga' => $subtotal + $ongkir,
            'status'      => 0, 
        ];

        // Load models
        $transactionModel = new \App\Models\TransactionModel();
        $transactionDetailModel = new \App\Models\TransactionDetailModel();

        // insert transaction
        if (!$transactionModel->insert($transaction)) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        $transactionId = $transactionModel->getInsertID();

        // insert transaction detail
        foreach ($cartItems as $item) {
            $transactionDetailModel->insert([
                'transaction_id' => $transactionId,
                'product_id'     => $item['id'],
                'jumlah'         => $item['qty'],
                'diskon'         => 0,
                'subtotal_harga' => $item['qty'] * $item['price'] 
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi');
        }

        //hapus session keranjang belanja 
        $this->cart->destroy();
        return redirect()->to(base_url())->with('success', 'Transaksi berhasil dibuat');
    }

}
