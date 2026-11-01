document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('shop-purchase-form');
    if (!form) return;
    var dock = document.querySelector('[data-purchase-dock]');
    var quantity = form.querySelector('[name=qty]');
    var minimum = Number(form.dataset.minimum), maximum = Number(form.dataset.maximum);
    var basePrice = Number(form.dataset.price);
    var error = form.querySelector('[data-purchase-error]');
    var lists = Array.from(document.querySelectorAll('[data-option-selections]'));
    var fields = form.querySelector('[data-purchase-fields]');
    var selected = new Map();
    var picker = form.querySelector('[data-purchase-picker]');
    var submitButtons = Array.from(form.querySelectorAll('button[type=submit]')).concat(Array.from(dock.querySelectorAll('button[type=submit]')));
    document.body.classList.add('has-shop-purchase-dock');
    function money(n) {return n.toLocaleString('ko-KR')+'원';}
    function sync() {
        var total = 0, count = 0;
        fields.replaceChildren();
        selected.forEach(function(item, option) {
            total += item.qty * item.price; count += item.qty;
            var i = fields.children.length / 2;
            [['option',option],['qty',item.qty]].forEach(function(pair){var input=document.createElement('input');input.type='hidden';input.name='options['+i+']['+pair[0]+']';input.value=pair[1];fields.append(input);});
        });
        if (!picker) {count=Number(quantity.value);var unit=basePrice;var projected=Number(form.dataset.jointQuantity||0)+count;var tiers=JSON.parse(form.dataset.jointTiers||'[]');var tier=tiers.find(function(t){return projected>=Number(t.min_quantity)&&(t.max_quantity===null||projected<=Number(t.max_quantity));});if(tier)unit=Number(tier.unit_price);total=count*unit;form.querySelector('[data-option-unit-price]').textContent=money(unit);}
        form.querySelector('[data-price-total]').textContent=money(total);
        dock.querySelector('[data-dock-total]').textContent=money(total);
        if (picker) picker.required = selected.size === 0;
        lists.forEach(function(list){Array.from(list.children).forEach(function(row){var item=selected.get(row.dataset.option);if(!item)return;var input=row.querySelector('input');if(input!==document.activeElement)input.value=item.qty;row.querySelector('output').textContent=money(item.qty*item.price);});});
        var invalid = count > 0 && (count < minimum || count > maximum);
        error.textContent=invalid?'총 수량은 '+minimum+'~'+maximum+'개로 선택해 주세요.':'';
        error.hidden=!invalid;
        submitButtons.forEach(function(button){button.disabled=maximum<minimum || invalid;});
        quantity.setCustomValidity(!picker && invalid ? error.textContent : '');
    }
    function render() {
        lists.forEach(function(list){
            list.replaceChildren();
            selected.forEach(function(item, option){
                var row=document.createElement('div');row.className='shop-selected-option';row.dataset.option=option;
                var name=document.createElement('strong');name.textContent=option;
                var stepper=document.createElement('div');stepper.className='shop-stepper';
                var input=document.createElement('input');input.type='number';input.min='1';input.max=item.max;input.value=item.qty;input.setAttribute('aria-label',option+' '+(list.dataset.optionSelections==='dock'?'하단 ':'')+'수량');
                var minus=document.createElement('button'),plus=document.createElement('button');minus.type=plus.type='button';minus.textContent='−';plus.textContent='+';minus.setAttribute('aria-label',option+' '+(list.dataset.optionSelections==='dock'?'하단 ':'')+'수량 줄이기');plus.setAttribute('aria-label',option+' '+(list.dataset.optionSelections==='dock'?'하단 ':'')+'수량 늘리기');
                function update(){item.qty=Math.max(1,Math.min(item.max,Number(input.value)||1));input.value=item.qty;sync();form.dispatchEvent(new Event('input',{bubbles:true}));}
                minus.addEventListener('click',function(){input.stepDown();update();});plus.addEventListener('click',function(){input.stepUp();update();});input.addEventListener('input',function(){item.qty=Math.max(1,Math.min(item.max,Number(input.value)||1));sync();});input.addEventListener('change',update);
                stepper.append(minus,input,plus);
                var price=document.createElement('output');price.textContent=money(item.qty*item.price);
                var remove=document.createElement('button');remove.type='button';remove.className='shop-option-remove';remove.textContent='×';remove.setAttribute('aria-label',option+' '+(list.dataset.optionSelections==='dock'?'하단 ':'')+'삭제');remove.addEventListener('click',function(){selected.delete(option);render();sync();});
                row.append(name,stepper,price,remove);list.append(row);
            });
        });
    }
    if (picker) {
        quantity.closest('.shop-option-row').hidden=true;quantity.disabled=true;
        var dockPicker=dock.querySelector('[data-purchase-picker]');
        Array.from(picker.options).forEach(function(option){dockPicker.append(option.cloneNode(true));});
        [picker,dockPicker].forEach(function(select){select.addEventListener('change',function(){
            if (!select.value) return;
            var option=select.selectedOptions[0];var max=Math.min(maximum,Number(option.dataset.stock ?? maximum));
            if(max<1){error.textContent='품절된 옵션입니다.';error.hidden=false;select.value='';return;}
            if(!selected.has(select.value) && selected.size<20) selected.set(select.value,{qty:1,price:basePrice+Number(option.dataset.priceAdjustment||0),max:max});
            picker.value=dockPicker.value='';render();sync();
        });});
    } else {quantity.addEventListener('input',sync);}
    form.addEventListener('submit',function(event){if(picker && !selected.size){event.preventDefault();picker.focus();picker.reportValidity();}sync();});
    sync();
});
