# FishPig_Smtp
This module allows you to use your Google (GSuite or GMail) account to send your Magento 2 emails.

This isn't the first free module to offer this functionality, but I think it's the first to do so without any bloat.

    composer require fishpig/magento2-smtp && \
    bin/magento module:enable FishPig_Smtp && \
    bin/magento setup:upgrade --keep-generated
