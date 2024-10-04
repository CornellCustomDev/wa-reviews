<div style="display: inline-block" x-data="{ analyzeEntirePage: $wire.entangle('entirePage') }">
    <button wire:click="analyze">{{ $scope->pageHasBeenRetrieved() ? 'Re-analyze' : 'Analyze' }}</button>
    <label><input type="checkbox" id="analyzeEntirePage" x-model="analyzeEntirePage" /> entire page</label>
    <span wire:loading.delay wire:target="analyze">| Processing...</span>
</div>
