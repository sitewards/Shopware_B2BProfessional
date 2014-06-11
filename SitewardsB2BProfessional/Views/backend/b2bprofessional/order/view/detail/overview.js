//{block name="backend/order/view/detail/overview" append}
//{namespace name="backend/b2bprofessional/main"}
Ext.define('Shopware.apps.Order.view.detail.Overview.b2bprofessional.Overview', {

    override: 'Shopware.apps.Order.view.detail.Overview',

    createLeftDetailElements: function() {
        var oThis = this;
        var aFields = oThis.callParent(arguments);

        return Ext.Array.insert(
            aFields,
            3,
            [
                {
                    name: 'b2bprofessionalDeliveryDate',
                    fieldLabel: '{s name="B2BProfessionalDeliveryDate"}Lieferdatum{/s}'
                }
            ]
        );
    }
});
//{/block}