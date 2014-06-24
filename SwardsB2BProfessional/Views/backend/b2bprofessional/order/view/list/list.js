//{block name="backend/order/view/list/list" append}
//{namespace name="backend/b2bprofessional/main"}
Ext.define('Shopware.apps.Order.view.list.List.b2bprofessional.List', {

    override: 'Shopware.apps.Order.view.list.List',


    getColumns : function() {
        var oThis = this;

        var aColumns = oThis.callParent(arguments);

        return Ext.Array.insert(
            aColumns,
            1,
            [
                {
                    header: '{s name="B2BProfessionalDeliveryDate"}Lieferdatum{/s}',
                    dataIndex: 'b2bprofessionalDeliveryDate',
                    flex: 1
                }
            ]
        );
    }

});
//{/block}