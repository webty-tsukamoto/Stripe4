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

namespace Plugin\Stripe4\Tests\Web\Admin;


use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

class ConfigControllerTest extends AbstractAdminWebTestCase
{
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    public function testページ表示チェック()
    {
        $this->client->request('GET', $this->generateUrl('stripe4_admin_config'));

        self::assertTrue($this->client->getResponse()->isSuccessful());
    }

}
