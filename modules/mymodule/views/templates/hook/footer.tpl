<!-- Block mymodule -->
<div id="mymodule_block_home" class="col-md-12">
    <div class="col-md-4 card">
        <div class="card-body">
            {if isset($minRange) && $minRange}
                <p>{$data} товаров в диапазоне от <span>{$minRange}</span> до {$maxRange}</p>
            {/if}
        </div>
    </div>
</div>

<!-- /Block mymodule -->