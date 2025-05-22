<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage_invoices') || $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Super admin can view any invoice
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Users with manage_invoices permission can view invoices
        if ($user->hasPermissionTo('manage_invoices')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_invoices') || $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Super admin can update any invoice
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Users with manage_invoices permission can update invoices
        if ($user->hasPermissionTo('manage_invoices')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Only allow deletion if invoice is in draft status to prevent data integrity issues
        if ($invoice->status !== 'draft') {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               ($user->hasPermissionTo('manage_invoices') && $invoice->status === 'draft');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        // No one should be able to permanently delete invoices for audit purposes
        return false;
    }
}
