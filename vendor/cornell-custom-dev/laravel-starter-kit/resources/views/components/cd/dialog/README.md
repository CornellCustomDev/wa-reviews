# DIALOG

NOTE: you must add the component
```
    <x-cd.dialog.component-scripts />
```
to the head element of the page before using the `cd.dialog` component.

The modal dialog component is used as follows:

```
    <x-cd.dialog>
        <x-cd.dialog.button>
            <x-cd.form.submitbutton value="Open Modal"/>
        </x-cd.dialog.button>
        <x-cd.dialog.panel>
                <div>
                <h2>This is my modal</h2>
                <p>Isn't it great?</p>
                </div>
        </x-cd.dialog.panel>
    </x-cd.dialog>

```

You can use any markup in `<x-cd.dialog.button>`, e.g., a button, link, etc.  The click handler is already included in `cd-dialog.button`, so clicking on your element will open the dialog. 

If you would like to open the dialog from a Livewire component, add a `wire:model` atribute to the `<x-cd.dialog>` tag, like the following:

```
<x-cd-dialog wire:model="dialogFlag">
    ....
</x-cd-dialog>
```

When dialogFlag (or whatever property you use) becomes true, the dialog will open. 

Any content you include in `<x-cd.dialog.panel>` will be displayed in the dialog when it opens. 

The modal dialog has a close button in the upper right corner.  You can add a close button to your panel content by including

```
<x-cd.dialog.close-button value="Close" />
```



