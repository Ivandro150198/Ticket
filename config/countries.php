<?php

/**
 * Países suportados: moeda e métodos de pagamento.
 */
return [
    'PT' => [
        'name' => 'Portugal',
        'currency' => 'EUR',
        'currency_label' => 'Euro (€)',
        'locale' => 'pt_PT',
        'payments' => [
            'simulado' => 'Pagamento simulado (imediato)',
            'mbway' => 'MB Way',
            'multibanco' => 'Multibanco',
            'cartao' => 'Cartão bancário',
        ],
    ],
    'GW' => [
        'name' => 'Guiné-Bissau',
        'currency' => 'XOF',
        'currency_label' => 'Franco CFA (FCFA)',
        'locale' => 'pt_GW',
        'payments' => [
            'simulado' => 'Pagamento simulado (imediato)',
            'orange_money' => 'Orange Money',
            'transfer_uemoa' => 'Transferência bancária (UEMOA)',
            'cartao' => 'Cartão bancário',
        ],
    ],
];
