import { Head } from '@inertiajs/react';
import SavedDesigns from '../../../components/customer/SavedDesigns';
import CustomerAccountLayout from '../../../layouts/CustomerAccountLayout';

export default function SavedDesignsPage() {
  return (
    <>
      <Head title="Saved Designs" />

      <CustomerAccountLayout active="designs">
        <SavedDesigns />
      </CustomerAccountLayout>
    </>
  );
}