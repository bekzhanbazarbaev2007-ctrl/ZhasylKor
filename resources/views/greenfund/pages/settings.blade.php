<div class="page-heading">
    <div><div class="eyebrow">ACCOUNT <span class="eyebrow-divider">/</span> PREFERENCES</div><h1>Settings</h1><p>Manage your profile and account security.</p></div>
</div>
<section class="panel settings-panel">
    <h2>Profile and security</h2>
    <p>Update your contact details, password, passkeys, and two-factor authentication.</p>
    <a class="button button-primary" href="{{ route('profile.edit') }}">Open profile settings</a>
</section>
<section class="panel settings-panel">
    <h2>Current role</h2>
    <p>{{ $roleLabel }} access is enforced by server-side role checks.</p>
</section>
