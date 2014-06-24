//{block name="backend/order/model/order/fields" append}
{literal}
{
    name: 'b2bprofessionalDeliveryDate',
    type: 'string',
    convert: function (value, record) {
        if (!value) {
            value = record.raw.attribute.b2bprofessionalDeliveryDate;
        }
        return value;
    }
},
{/literal}
//{/block}