# Custom Gateway integration Sample

This is the skeleton or wireframe for creating a plugin to add custom support to your payment gateway for the CartFlows upsell and downsell features.

To start adding the custom support, follow this article: https://cartflows.com/docs/add-custom-support-of-any-payment-gateway/

The custom file in which you have added support of your payment gateway for CartFlows upsell/downsell should be stored/saved in the folder/directory named as `gateway-files`.

## Directory Information.
- <b>Classes:</b> The directory which holds plugin loader and the supportive/helper classes files.
    ##### Files in the `Classes` directory: 
    - <b>loader.php:</b> This file is the main loader of the plugin which will initialize all of your gateway classes mentioned in the `add_custom_gateway_integration` function. You need to add your own integration info in the same format to load the required classes.

- <b>gateway-files:</b> In this directory you need to store your gateway integration files. These files will holds your main logic to process the payment for upsell/downsell for your payment gateway.