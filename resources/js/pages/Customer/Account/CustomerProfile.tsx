import { Head } from '@inertiajs/react';
import CustomerProfile from '../../../components/customer/CustomerProfile';
import CustomerAccountLayout from '../../../layouts/CustomerAccountLayout';

export default function CustomerProfilePage() {
  return (
    <>
      <Head title="My Profile" />

      <CustomerAccountLayout active="profile">
        <CustomerProfile />
      </CustomerAccountLayout>
    </>
  );
}
