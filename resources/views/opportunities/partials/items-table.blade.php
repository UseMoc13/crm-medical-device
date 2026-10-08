@if($items->count())

<table>

    <thead>

        <tr>

            <th>
                Product
            </th>

            <th>
                Quantity
            </th>

            <th>
                Estimated Price
            </th>

            <th>
                Subtotal
            </th>

            <th>
                Notes
            </th>

            <th>
                Actions
            </th>

        </tr>

    </thead>


    <tbody>

        @foreach($items as $item)

            <tr>

                <td>

                    @if($item->product)

                        <div class="product-name">
                            {{ $item->product->product_name }}
                        </div>

                        @if($item->product->product_code)

                            <div class="product-code">
                                {{ $item->product->product_code }}
                            </div>

                        @endif

                        @if($item->product->product_type)

                            <span
                                data-product-type="{{ $item->product->product_type }}"
                                style="display:none;">
                            </span>

                        @endif

                    @else

                        <span class="muted">
                            Product unavailable
                        </span>

                    @endif

                </td>


                <td>

                    {{ number_format(
                        (int) $item->quantity
                    ) }}

                </td>


                <td>

                    @if($item->estimated_price !== null)

                        Rp
                        {{ number_format(
                            (float) $item->estimated_price,
                            0,
                            ',',
                            '.'
                        ) }}

                    @else

                        -

                    @endif

                </td>


                <td>

                    @if($item->estimated_price !== null)

                        <strong class="subtotal">

                            Rp
                            {{ number_format(
                                (float) $item->estimated_price *
                                (int) $item->quantity,
                                0,
                                ',',
                                '.'
                            ) }}

                        </strong>

                    @else

                        -

                    @endif

                </td>


                <td class="notes-cell">

                    {{ $item->notes ?: '-' }}

                </td>


                <td>

                    <div class="item-row-actions">

                        <a
                            href="{{ route(
                                'opportunities.items.edit',
                                [
                                    'opportunity' => $opportunity,
                                    'item' => $item
                                ]
                            ) }}"
                            class="text-action">

                            Edit

                        </a>


                        <form
                            method="POST"
                            action="{{ route(
                                'opportunities.items.destroy',
                                [
                                    'opportunity' => $opportunity,
                                    'item' => $item
                                ]
                            ) }}"
                            onsubmit="return confirm('Delete this opportunity item?');">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="text-action">

                                Delete

                            </button>

                        </form>

                    </div>

                </td>

            </tr>

        @endforeach

    </tbody>

</table>

@else

<div class="items-empty">

    <strong>
        No items found.
    </strong>

    <span>
        Try changing the search or filter.
    </span>

</div>

@endif