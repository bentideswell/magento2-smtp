<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\Smtp\Model\Config\Source;

class MailProvider
{
    /**
     * @const int
     */
    const TYPE_GOOGLE = 1;
    const TYPE_OFFICE365 = 2;
    const TYPE_SMTP = 9;
    /**
     * @param  bool  $includeEmpty = true
     * @return array
     */
    public function getOptions($includeEmpty = true) : array
    {
        $options = [
            self::TYPE_GOOGLE => __('Google'),
            self::TYPE_OFFICE365 => __('Office 365'),
            self::TYPE_SMTP => __('SMTP')
        ];

        if ($includeEmpty) {
            array_unshift($options, [0 => __('-- Please Select')]);
        }

        return $options;
    }

    /**
     * @param  bool  $includeEmpty = true
     * @return array
     */
    public function toOptionArray($includeEmpty = true) : array
    {
        $options = [];

        foreach ($this->getOptions($includeEmpty) as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @param  int $typeId
     * @return bool
     */
    public function isValidMailProvider(int $typeId) : bool
    {
        $options = $this->getOptions(false);

        return isset($options[$typeId]);
    }
}
