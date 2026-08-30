@php($firstVisitPoints = old('first_visit_points', isset($shop) ? $shop->first_visit_points : 0))
<div class="ttl01 mt40">첫 방문 포인트</div>
<div class="tb01">
    <table>
        <colgroup><col width="175px"><col width=""></colgroup>
        <tbody class="textL">
            <tr>
                <th class="w160"><span>첫 방문 적립</span></th>
                <td>
                    <input type="number" name="first_visit_points" value="{{ $firstVisitPoints }}" min="0" max="100000" step="1" class="w160"> P
                    <div class="col2 fs2 mt5">0P는 미사용입니다. 회원별 해당 Shop 채널 첫 방문 1회에만 적립되며 채널 보유 포인트에서 차감됩니다.</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
