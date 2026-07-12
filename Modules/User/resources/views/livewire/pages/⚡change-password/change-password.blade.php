<form wire:submit="save">
    <input type="password" wire:model="form.password">
    @error('form.password')
        <span class='bg-red-500 rounded-xs p-2'>$error</span>
    @enderror
    <input type="password" wire:model="form.password_confirmation">
 
    <button type="submit">Save</button>
</form>