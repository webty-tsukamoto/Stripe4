<?php
/**
 * This file is part of Stripe4
 *
 * Copyright(c) Akira Kurozumi <info@a-zumi.net>
 *
 * https://a-zumi.net
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Stripe4\Tests\Web;


use Eccube\Common\Constant;
use Eccube\Entity\Delivery;
use Eccube\Entity\Payment;
use Eccube\Entity\PaymentOption;
use Eccube\Repository\DeliveryRepository;
use Eccube\Repository\PaymentRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Tests\Web\AbstractShoppingControllerTestCase;
use Plugin\Stripe4\Service\Method\CreditCard;

class ShoppingControllerTest extends AbstractShoppingControllerTestCase
{
    /**
     * @var DeliveryRepository
     */
    private $deliveryRepository;

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $container = self::$kernel->getContainer();

        $this->deliveryRepository = $container->get(DeliveryRepository::class);
        $this->paymentRepository = $container->get(PaymentRepository::class);
        $this->productRepository = $container->get(ProductRepository::class);
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    public function testお支払い方法にクレジットカード決済が表示されるか()
    {
        /** @var Delivery $delivery 販売種別Aのサンプル業者 */
        $delivery = $this->deliveryRepository->find(1);

        // 販売種別Aにクレジットカード決済を登録
        $this->createPaymentOption($delivery);

        $Customer = $this->createCustomer();

        // カート画面
        $this->scenarioCartIn($Customer);

        // 確認画面
        $crawler = $this->scenarioConfirm();

        self::assertContains("クレジットカード決済", $crawler->html());
    }

    public function testクレジットカード決済を選択したときにクレジットカード情報項目が表示されるか()
    {
        /** @var Delivery $delivery 販売種別Aのサンプル業者 */
        $delivery = $this->deliveryRepository->find(1);

        // 販売種別Aにクレジットカード決済を登録
        $paymentOption = $this->createPaymentOption($delivery);

        $Customer = $this->createCustomer();

        // カート画面
        $this->scenarioCartIn($Customer);

        // 確認画面
        $this->scenarioConfirm($Customer);

        // デフォルトがクレジットカード決済になっているので一度別の決済方法に変更
        $crawler = $this->scenarioRedirectTo($Customer, [
            '_shopping_order' => [
                'Shippings' => [
                    [
                        'Delivery' => $delivery->getId(),
                        'DeliveryTime' => null,
                    ],
                ],
                'Payment' => 1,
                Constant::TOKEN_NAME => '_dummy',
            ]
        ]);

        self::assertNotContains("クレジットカード情報", $crawler->html());

        // クレジットカード決済を選択
        $crawler = $this->scenarioRedirectTo($Customer, [
            '_shopping_order' => [
                'Shippings' => [
                    [
                        'Delivery' => $delivery->getId(),
                        'DeliveryTime' => null,
                    ],
                ],
                'Payment' => $paymentOption->getPaymentId(),
                Constant::TOKEN_NAME => '_dummy'
            ]
        ]);

        self::assertContains("クレジットカード情報", $crawler->html());
    }

    /**
     * @param Delivery $delivery
     * @return PaymentOption
     */
    private function createPaymentOption(Delivery $delivery): PaymentOption
    {
        /** @var Payment $payment クレジットカード決済 */
        $payment = $this->paymentRepository->findOneBy([
            "method_class" => CreditCard::class
        ]);

        $paymentOption = new PaymentOption();
        $paymentOption
            ->setDeliveryId($delivery->getId())
            ->setDelivery($delivery)
            ->setPaymentId($payment->getId())
            ->setPayment($payment);
        $this->entityManager->persist($paymentOption);
        $this->entityManager->flush();

        return $paymentOption;
    }
}
