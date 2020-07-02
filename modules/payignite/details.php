<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Module_Payignite extends Module
{
    public $version = '1.1';

    public function info()
    {
        return array(
            'name' => array(
                'en' => 'Payignite',
            ),
            'description' => array(
                'en' => 'Stripe Payignite',
            ),
            'frontend' => true,
            'backend' => true,
            'menu' => 'content',
            'sections' => array(
                'invoice' => array(
                    'name' => 'payignite:invoice',
                    'uri' => 'admin/payignite',
                    'shortcuts' => array(
                        'create' => array(
                            'name' => 'payignite:new',
                            'uri' => 'admin/payignite/create',
                            'class' => 'add',
                        ),
                    ),
                ),
                'payments' => array(
                    'name' => 'payignite:payments',
                    'uri' => 'admin/payignite/payments',
                    'shortcuts' => array(
                        /*'create' => array(
                            'name' => 'payignite:payment',
                            'uri' => 'admin/payignite/payments/create',
                            'class' => 'add'
                        )*/
                    ),
                ),
                'coupons' => array(
                    'name' => 'payignite:coupons',
                    'uri' => 'admin/payignite/coupons',
                    'shortcuts' => array(
                        'create' => array(
                            'name' => 'payignite:coupon',
                            'uri' => 'admin/payignite/coupons/create',
                            'class' => 'add',
                        ),
                    ),
                ),
                'plans' => array(
                    'name' => 'payignite:plans',
                    'uri' => 'admin/payignite/plans',
                    'shortcuts' => array(
                        'create' => array(
                            'name' => 'payignite:plan',
                            'uri' => 'admin/payignite/plans/create',
                            'class' => 'add',
                        ),
                    ),
                ),
                /*
                'subscriptions' => array(
                    'name' 	=> 'payignite:subscriptions',
                    'uri' 	=> 'admin/payignite/subscriptions',
                        'shortcuts' => array(
                            'create' => array(
                                'name' 	=> 'payignite:create',
                                'uri' 	=> 'admin/payignite/subscriptions/create',
                                'class' => 'add'
                            )
                        )
                ),
                */
                'customers' => array(
                    'name' => 'payignite:customers',
                    'uri' => 'admin/payignite/customers',
                    'shortcuts' => array(
                        'create' => array(
                            'name' => 'payignite:customer',
                            'uri' => 'admin/payignite/customers/create',
                            'class' => 'add',
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Install.
     *
     * This function will set up our
     * FAQ/Category streams.
     */
    public function install()
    {
        // We're using the streams API to
        // do data setup.
        $this->db->delete('settings', array('module' => 'payignite'));
        $this->load->driver('Streams');
        $this->streams->utilities->remove_namespace('payignite');
        $this->load->language('payignite/payignite');

        // Add payignite streams
        // if (!$customer_user_stream_id = $this->streams->streams->add_stream('lang:payignite:customer_user', 'customer_user', 'payignite', 'payignite_', null)) {
        // return false;
        // }
        //if (!$cards_stream_id = $this->streams->streams->add_stream('lang:payignite:cards', 'cards', 'payignite', 'payignite_', null)) {
        //    return false;
        //}
        if (!$subscriptions_stream_id = $this->streams->streams->add_stream('lang:payignite:subscriptions', 'subscriptions', 'payignite', 'payignite_', null)) {
            return false;
        }

        // Add some fields
        $fields = array(

            /*Customer to User*/
            /*
            array(
                'name' => 'payignite:user_id',
                'slug' => 'customer_user_id',
                'namespace' => 'payignite',
                'type' => 'user',
                'extra' => array('restrict_group' => 'customer'),
                'assign' => 'customer_user',
                'title_column' => true,
                'required' => true,
                'unique' => true,
            ),
            array(
                'name' => 'payignite:customer_id',
                'slug' => 'customer_user_customer_id',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 200),
                'assign' => 'customer_user',
                //'title_column' => true,
                'required' => true,
            ),
            */

            /*Cards*/
            /*
            array(
                'name' => 'payignite:card_id',
                'slug' => 'card_id',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 100),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'payignite:card_last4',
                'slug' => 'card_last4',
                'namespace' => 'payignite',
                'type' => 'integer',
                'extra' => array('max_length' => 4),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'payignite:customer_id',
                'slug' => 'card_customer_id',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 100),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'payignite:customer_email',
                'slug' => 'customer_email',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 100),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'payignite:exp_time',
                'slug' => 'exp_time',
                'namespace' => 'payignite',
                'type' => 'integer',
                'extra' => array('max_length' => 30),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'payignite:mail_time',
                'slug' => 'mail_time',
                'namespace' => 'payignite',
                'type' => 'integer',
                'extra' => array('max_length' => 30),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'payignite:email_sent',
                'slug' => 'email_sent',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 5, 'default_value' => 'false'),
                'assign' => 'cards',
                'title_column' => true,
                'required' => true,
            ),
            */
            /* Subscriptions */
            array(
                'name' => 'Customer ID',
                'slug' => 'sub_customer_id',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 50),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'Subscription ID',
                'slug' => 'sub_subscription_id',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 50),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'Plan ID',
                'slug' => 'sub_plan_id',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 20),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => true,
            ),
            array(
                'name' => 'Payment Interval',
                'slug' => 'sub_interval',
                'namespace' => 'payignite',
                'type' => 'integer',
                'extra' => array('max_length' => 2),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => true,
            ),
            /*
            array(
                'name' => 'payignite:customer_email',
                'slug' => 'sub_customer_email',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 100),
                'assign' => 'subscriptions',
                'title_column' => true,
                'required' => false,
            ),

            array(
                'name' => 'payignite:exp_time',
                'slug' => 'sub_exp_time',
                'namespace' => 'payignite',
                'type' => 'integer',
                'extra' => array('max_length' => 30),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => false,
            ),
            array(
                'name' => 'payignite:mail_time',
                'slug' => 'sub_mail_time',
                'namespace' => 'payignite',
                'type' => 'integer',
                'extra' => array('max_length' => 30),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => false,
            ),
            array(
                'name' => 'payignite:email_sent',
                'slug' => 'sub_email_sent',
                'namespace' => 'payignite',
                'type' => 'text',
                'extra' => array('max_length' => 5, 'default_value' => 'false'),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => false,
            ),
            */
            /* relations
            array(
                'name' => 'payignite:subscription_id',
                'slug' => 'subscription_id',
                'namespace' => 'payignite',
                'type' => 'relationship',
                'extra' => array('choose_stream' => $subscriptions_stream_id),
                'assign' => 'subscriptions',
                //'title_column' => true,
                'required' => true,
            ),
            */
        );

        $this->streams->fields->add_fields($fields);
        /*
        $this->streams->streams->update_stream('customer_user', 'payignite', array(
            'view_options' => array(
                'customer_user_id',
                'customer_user_customer_id',
            ),
        ));
        */
        /*
         $this->streams->streams->update_stream('cards', 'payignite', array(
             'view_options' => array(
                 'card_id',
                 'card_last4',
                 'customer_id',
                 'customer_email',
                 'exp_time',
                 'mail_time',
                 'email_sent',
             ),
         ));
         */
        $this->streams->streams->update_stream('subscriptions', 'payignite', array(
            'view_options' => array(
                'user_id',
                'customer_id',
                'subscription_id',
                'plan_id',
                'interval',
            ),
        ));
        //Payignite settings
        $payignite_setting_secret_key = array(
            'slug' => 'payignite_setting_secret_key',
            'title' => 'Your Stripe Secret Key',
            'description' => 'Enter your stripe secret key here',
            '`default`' => '',
            '`value`' => '',
            '`options`' => '',
            'type' => 'text',
            'is_required' => 1,
            'is_gui' => 1,
            'module' => 'payignite',
        );
        /*
        $payignite_setting_publishable_key = array(
            'slug' => 'payignite_setting_stripe_publishable_key',
            'title' => 'Your Stripe Publishable Key',
            'description' => 'Enter your stripe publishable key for testing here',
            '`default`' => '',
            '`value`' => '',
            '`options`' => '',
            'type' => 'text',
            'is_required' => 1,
            'is_gui' => 1,
            'module' => 'payignite',
        );
        */
        $this->dbforge->add_key('id', true);

        if ($this->db->insert('settings', $payignite_setting_secret_key)
        //&& $this->db->insert('settings', $payignite_setting_publishable_key)
        && is_dir($this->upload_path.'payignite')
        || @mkdir($this->upload_path.'payignite', 0644, true)) { // was 777
            return true;
        }

        return true;
    }

    /**
     * Uninstall.
     *
     * Uninstall our module - this should tear down
     * all information associated with it.
     */
    public function uninstall()
    {
        $this->db->delete('settings', array('module' => 'payignite'));
        $this->load->driver('Streams');

        // For this teardown we are using the simple remove_namespace
        // utility in the Streams API Utilties driver.
        $this->streams->utilities->remove_namespace('payignite');

        return true;
    }

    public function upgrade($old_version)
    {
        switch ($old_version) {
            case '1.0':
                $payignite_setting_public_key = array(
                    'slug' => 'payignite_setting_public_key',
                    'title' => 'Your Stripe Public Key',
                    'description' => 'Enter your stripe public key here',
                    '`default`' => '',
                    '`value`' => '',
                    '`options`' => '',
                    'type' => 'text',
                    'is_required' => 1,
                    'is_gui' => 1,
                    'module' => 'payignite',
                );

                $this->db->insert('settings', $payignite_setting_public_key);
                break;
        }
        return true;
    }

    public function help()
    {
        // Return a string containing help info
        // You could include a file and return it here.
        return 'No documentation has been added for this module.<br />Contact the module developer for assistance.';
    }
    public function admin_menu(&$menu)
    {
        $this->load->language('payignite/payignite');
        $menu['Payignite'] = array(
            'lang:payignite:invoice' => 'admin/payignite',
            'lang:payignite:payments' => 'admin/payignite/payments',
            'lang:payignite:coupons' => 'admin/payignite/coupons',
            'lang:payignite:plans' => 'admin/payignite/plans',
            'lang:payignite:customers' => 'admin/payignite/customers',
        );
        add_admin_menu_place('Payignite', 1);
    }
}
