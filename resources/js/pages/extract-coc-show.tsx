import { Head, Link, usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';
import Heading from '@/components/heading';
import { Button, buttonVariants } from '@/components/ui/button';

import AppLogoIcon from '@/components/app-logo-icon';

export default function ExtractCoc() {
    const { data } = usePage<SharedData>().props;

    console.log(usePage<SharedData>().props);
    console.log(data);

    return (
        <div className="p-6 max-w-2xl mx-auto">
            <Head title="Extracted CoC" />
            <h1 className="text-xl font-semibold mb-10">Extracted Conformity Certificate</h1>

            <Heading title="Uploaded image" description="This is what you uploaded." />
            <img src={data?.image} alt="Coc File" />

            <div className="my-10">
                <Heading title="Answer"
                         description="Here is the answer from the LLM and we are TRYING to display in JSON using prompt." />

                {!data?.result && <div className="text-muted-foreground text-sm text-red-500 mb-2">*Extraction was not successful, but here is the response from the LLM.</div>}

                {data?.result && <div className="text-sm text-green-500 mb-2">*Extraction was successful, here is the json response from the LLM.</div>}

                <div className="whitespace-pre-wrap break-words mb-4">
                    {data?.result
                        ? JSON.stringify(JSON.parse(data?.result || '{}'), null, 2)
                        : data?.answer
                    }
                </div>

            </div>

            <Button asChild className="w-full">
                <Link href={route('home')}>Try another one</Link>
            </Button>
        </div>
    );
}
