import { HandHeart, Flag, GraduationCap, Check } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

interface ProgramItem {
  name: string;
  note?: string;
}

interface Pillar {
  title: string;
  icon: LucideIcon;
  color: {
    header: string;
    iconBg: string;
    check: string;
  };
  items: ProgramItem[];
}

const pillars: Pillar[] = [
  {
    title: 'Abdi Agama',
    icon: HandHeart,
    color: { header: 'bg-blue-600', iconBg: 'bg-blue-500', check: 'text-blue-600' },
    items: [
      { name: 'Angkasa Cerdas Spiritual' },
      { name: 'Angkasa Cinta Dirgantara' },
    ],
  },
  {
    title: 'Abdi Bangsa dan Negara',
    icon: Flag,
    color: { header: 'bg-blue-600', iconBg: 'bg-blue-500', check: 'text-blue-600' },
    items: [
      { name: 'Angkasa Berkarakter' },
      { name: 'Angkasa Berbudaya' },
      { name: 'Angkasa Ramah Lingkungan' },
      { name: 'Angkasa Fullday School' },
      { name: 'Angkasa Berkarya', note: 'Mengadopsi dan mengadaptasi pembelajaran negara maju' },
      { name: 'Angkasa Can Speak' },
    ],
  },
  {
    title: 'Abdi Ilmu',
    icon: GraduationCap,
    color: { header: 'bg-blue-600', iconBg: 'bg-blue-500', check: 'text-blue-600' },
    items: [
      { name: 'Angkasa Berbakat' },
      { name: 'Angkasa Juara' },
    ],
  },
];

export default function Programs() {
  return (
    <section id="programs" className="py-16 bg-white scroll-mt-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold text-gray-900 mb-4">Program Unggulan</h2>
          <p className="text-xl text-gray-600">Struktur Kurikulum Multidimensi Sekolah Angkasa</p>
        </div>

        <div className="grid md:grid-cols-3 gap-8">
          {pillars.map((pillar) => {
            const Icon = pillar.icon;
            return (
              <div key={pillar.title} className="bg-gray-50 rounded-lg shadow-md overflow-hidden flex flex-col">
                {/* Header pilar */}
                <div className={`${pillar.color.header} text-white px-6 py-5 flex items-center gap-4`}>
                  <div className={`${pillar.color.iconBg} w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0`}>
                    <Icon className="w-6 h-6" />
                  </div>
                  <h3 className="text-xl font-bold uppercase tracking-wide">{pillar.title}</h3>
                </div>

                {/* Daftar program */}
                <ul className="p-6 space-y-3 flex-1">
                  {pillar.items.map((item) => (
                    <li key={item.name} className="flex gap-3">
                      <Check className={`w-5 h-5 mt-0.5 flex-shrink-0 ${pillar.color.check}`} />
                      <div>
                        <p className="font-semibold text-gray-800">{item.name}</p>
                        {item.note && <p className="text-sm text-gray-500">{item.note}</p>}
                      </div>
                    </li>
                  ))}
                </ul>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}