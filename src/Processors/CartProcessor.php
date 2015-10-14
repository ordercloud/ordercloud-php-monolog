<?php namespace Ordercloud\Monolog\Processors;

use Monolog\Logger;
use Ordercloud\Cart\CartService;
use Ordercloud\Cart\Exceptions\CartException;

class CartProcessor
{
    /**
     * @var CartService
     */
    private $carts;
    /**
     * @var string
     */
    private $cartId;
    /**
     * @var int
     */
    private $logLevel;

    /**
     * @param CartService $carts
     * @param string      $cartId
     * @param int         $logLevel
     */
    public function __construct(CartService $carts, $cartId, $logLevel = null)
    {
        $this->carts = $carts;
        $this->cartId = $cartId;
        $this->logLevel = $logLevel ?: Logger::DEBUG;
    }

    function __invoke($record)
    {
        if ($record['level'] >= $this->logLevel) {
            try {
                $record = $this->addCartContext($record);
            }
            catch (CartException $e) {
                $record['context']['cart'] = 'An error prevented adding cart context.';
            }
        }

        return $record;
    }

    /**
     * @param $record
     *
     * @return mixed
     */
    protected function addCartContext($record)
    {
        $cart = $this->carts->getCartById($this->cartId);

        $record['context']['cart'] = [
            'id'    => $cart->getId(),
            'note'  => $cart->getNote(),
            'total' => $cart->getTotalAmount(),
        ];

        foreach ($cart->getItems() as $cartItem) {
            $item = [
                'puid'     => $cartItem->getPuid(),
                'note'     => $cartItem->getNote(),
                'amount'   => $cartItem->getAmount(),
                'quantity' => $cartItem->getQuantity(),
                'product'  => [
                    'id'   => $cartItem->getProduct()
                        ->getId(),
                    'name' => $cartItem->getProduct()
                        ->getName(),
                ],
            ];

            foreach ($cartItem->getOptions() as $option) {
                $item['options'][] = [
                    'set'  => [
                        'id'   => $option->getOptionSet()
                            ->getId(),
                        'name' => $option->getOptionSet()
                            ->getName(),
                    ],
                    'id'   => $option->getOption()
                        ->getId(),
                    'name' => $option->getOption()
                        ->getName(),
                ];
            }

            foreach ($cartItem->getExtras() as $extra) {
                $item['extras'][] = [
                    'set'  => [
                        'id'   => $extra->getExtraSet()
                            ->getId(),
                        'name' => $extra->getExtraSet()
                            ->getName(),
                    ],
                    'id'   => $extra->getExtra()
                        ->getId(),
                    'name' => $extra->getExtra()
                        ->getName(),
                ];
            }

            $record['context']['cart']['items'][] = $item;
        }

        return $record;
    }
}
