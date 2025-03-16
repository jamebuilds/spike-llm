import { Head, Link, useForm } from '@inertiajs/react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import Heading from '@/components/heading';

export default function ExtractCoc() {
    const { data, setData, post, processing, errors } = useForm({
        coc_file: null
    });

    const handleFileChange = (event) => {
        const selectedFile = event.target.files[0];
        if (selectedFile) {
            setData('coc_file', selectedFile);
        }
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        post(route('extract-coc.store'));
    };

    return (
        <div className="p-6 max-w-2xl mx-auto">
            <Head title="Extract CoC" />

            <Heading title="Extract Conformity Certificate"
                     description="Try extracting a Conformity Certificate from an image using a prompt we created. The model we are using https://www.together.ai/models/llama-3-2." />

            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="grid gap-2">
                    <Label htmlFor="coc-file">Upload Certificate (.jpg,.jpeg,.png)</Label>
                    <Input
                        id="coc-file"
                        type="file"
                        accept=".jpg,.jpeg,.png"
                        onChange={handleFileChange}
                        required
                    />
                    {errors.coc_file && <p className="text-red-500 text-sm">{errors.coc_file}</p>}
                </div>

                <Button type="submit" disabled={processing} className="w-full">
                    {processing ? 'Uploading...' : 'Extract'}
                </Button>
            </form>

            <div className="mt-4 flex flex-col">
                <div>Some samples to test out:</div>
                <a
                    href={route('download-sample-coc.invoke', {
                        file_name: '22A0614.png'
                    })}
                    target="_blank"
                    rel="noopener noreferrer"
                    download="22A0614.png"
                >Download sample 1 (22A0614)</a>

                <a
                    href={route('download-sample-coc.invoke', {
                        file_name: 'CLS1B-081460-0025-Rev.-00.png'
                    })}
                    target="_blank"
                    rel="noopener noreferrer"
                    download="CLS1B-081460-0025-Rev.-00.png"
                >Download sample 1 (CLS1B 081460 0025 Rev. 00)</a>

                <a
                    href={route('download-sample-coc.invoke', {
                        file_name: 'FSP-2018-1188-DoC.png'
                    })}
                    target="_blank"
                    rel="noopener noreferrer"
                    download="FSP-2018-1188-DoC.png"
                >Download sample 1 (FSP-2018-1188)</a>
            </div>
        </div>
    );
}
