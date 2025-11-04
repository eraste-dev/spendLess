<?php

namespace App\Http\Controllers\Contact;

use App\Http\Requests\ContactMessageRequest;
use App\Repositories\ContactMessageRepository;
use App\Repositories\OrganizationRepository;

class ContactMessageController extends Controller
{
    public function submit(ContactMessageRequest $request)
    {
        $host = request()->getSchemeAndHttpHost();
        $organization = OrganizationRepository::query()->where('domain', $host)->first();

        ContactMessageRepository::create([
            'organization_id' => $organization ? $organization->id : null,
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return $this->json('Message received', null, 201);
    }
}
