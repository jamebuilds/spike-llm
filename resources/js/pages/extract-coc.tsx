import { useEffect, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

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

            <h1 className="text-xl font-semibold mb-10">Extract Conformity Certificate</h1>

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
        </div>
    );
}
