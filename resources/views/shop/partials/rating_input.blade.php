<fieldset class="me9-rating" data-rating-input>
    <legend>상품 평가 (선택)</legend>
    <div class="me9-rating-stars">
        <div class="me9-rating-empty" aria-hidden="true"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
        <div class="me9-rating-fill" aria-hidden="true"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
        @for($half = 1; $half <= 10; $half++)
            <input id="rating-{{ $ratingId }}-{{ $half }}" type="radio" name="rating" value="{{ $half / 2 }}" aria-label="{{ $half / 2 }}점">
            <label for="rating-{{ $ratingId }}-{{ $half }}" data-rating-value="{{ $half / 2 }}" style="left:{{ ($half - 1) * 10 }}%" title="{{ $half / 2 }}점"></label>
        @endfor
    </div>
    <output aria-live="polite">선택 안 함</output>
    <button type="button" class="me9-rating-clear">선택 취소</button>
</fieldset>
@once
<style>
.me9-rating{border:0;margin:16px 0;padding:0}.me9-rating legend{font-size:14px}.me9-rating-stars{position:relative;width:220px;height:44px;margin:6px 0;max-width:100%}.me9-rating-empty,.me9-rating-fill{position:absolute;left:0;top:0;width:220px;font-size:40px;line-height:44px;letter-spacing:0;font-family:monospace;white-space:nowrap;color:#c6c9cd;pointer-events:none}.me9-rating-empty span,.me9-rating-fill span{display:inline-block;width:44px;text-align:center}.me9-rating-fill{color:#b97608;width:0;overflow:hidden}.me9-rating-stars input{position:absolute;width:1px;height:1px;opacity:0}.me9-rating-stars label{position:absolute;top:0;width:10%;height:44px;margin:0;cursor:pointer}.me9-rating-stars input:focus-visible+label{outline:2px solid #187e77;outline-offset:2px}.me9-rating output{font-size:13px}.me9-rating-clear{border:0;border-bottom:1px solid currentColor;background:none;margin-left:12px;padding:4px;color:#555;font-size:12px}
</style>
<script src="{{ asset('shop/js/rating.js') }}" defer></script>
@endonce
