import { Head, useForm, usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';
import Heading from '@/components/heading';

export default function ExtractCoc() {
    const { data } = usePage<SharedData>().props;

    console.log(data);

    return (
        <div className="p-6 max-w-2xl mx-auto">
            <Head title="Extracted CoC" />
            <h1 className="text-xl font-semibold mb-10">Extracted Conformity Certificate</h1>

            {/*<Heading title="Uploaded image" description="This is what you uploaded." />*/}
            {/*<img src={data.image} alt="Coc File" />*/}

            <div className="my-10">
                <Heading title="Answer" description="Here is the answer from the LLM and we are trying to display in JSON using prompt." />
                <div className="whitespace-pre-wrap break-words mb-4">
                    {data.answer}
                </div>
            </div>
        </div>
    );
}
