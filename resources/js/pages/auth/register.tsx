import { RegisterForm } from '@/components/register-form';
import TextLink from '@/components/text-link';
import AuthLayout from '@/layouts/auth-layout';
import { Head } from '@inertiajs/react';

export default function Register() {
    return (
        <AuthLayout title="Create an account" description="Start with a private workspace for Static and Dynamic QR codes">
            <Head title="Register" />
            <RegisterForm />
            <div className="text-muted-foreground mt-6 text-center text-sm">
                Already have an account?{' '}
                <TextLink href={route('login')} tabIndex={6}>
                    Log in
                </TextLink>
            </div>
        </AuthLayout>
    );
}
