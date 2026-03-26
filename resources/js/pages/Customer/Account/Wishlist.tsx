import { Head } from '@inertiajs/react';
import Wishlist from '../../../components/customer/Wishlist';
import CustomerAccountLayout from '../../../layouts/CustomerAccountLayout';

export default function WishlistPage() {
  return (
    <>
      <Head title="My Wishlist" />

      <CustomerAccountLayout active="wishlist">
        <Wishlist />
      </CustomerAccountLayout>
    </>
  );
}