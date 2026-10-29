<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Sistem Penunjang Keputusan Kinerja Talenta IT';
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — PT Indobuzz Republik Digital</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#1E3A8A', // Deep Navy Blue
                            hover: '#172554',
                            light: '#2563EB',
                            subtle: '#EFF6FF',
                        },
                        accent: {
                            DEFAULT: '#0D9488', // Teal
                            hover: '#0F766E',
                            light: '#14B8A6',
                            subtle: '#F0FDFA',
                        },
                        canvas: '#F8FAFC',
                        surface: '#FFFFFF',
                        slateText: {
                            primary: '#0F172A',
                            muted: '#64748B',
                        },
                        status: {
                            success: '#16A34A',
                            warning: '#D97706',
                            danger: '#DC2626',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'Segoe UI', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Stylesheet for fine details -->
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #0F172A;
            background-color: #F8FAFC;
        }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background-color: #FFFFFF !important;
            }
        }
    </style>
</head>
<body class="h-full flex overflow-hidden">
