<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PasswordManagement\Controller\Adminhtml\Crypt\Key;

class Save extends \Magento\PasswordManagement\Controller\Adminhtml\Crypt\Key
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\PasswordManagement\Model\Resource\Key\Change
     */
    protected $change;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\PasswordManagement\Model\Resource\Key\Change $change
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\PasswordManagement\Model\Resource\Key\Change $change,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->encryptor = $encryptor;
        $this->change = $change;
        $this->cache = $cache;
        parent::__construct($context);
    }

    /**
     * Process saving new encryption key
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        try {
            $key = null;

            if (0 == $this->getRequest()->getPost('generate_random')) {
                $key = $this->getRequest()->getPost('crypt_key');
                if (empty($key)) {
                    throw new \Exception(__('Please enter an encryption key.'));
                }
                $this->encryptor->validateKey($key);
            }

            $newKey = $this->change->changeEncryptionKey($key);
            $this->messageManager->addSuccess(__('The encryption key has been changed.'));

            if (!$key) {
                $this->messageManager->addNotice(
                    __(
                        'This is your new encryption key: <span style="font-family:monospace;">%1</span>. Be sure to write it down and take good care of it!',
                        $newKey
                    )
                );
            }
            $this->cache->clean();
        } catch (\Exception $e) {
            if ($message = $e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->_session->setFormData(['crypt_key' => $key]);
        }
        $this->_redirect('adminhtml/*/');
    }
}
