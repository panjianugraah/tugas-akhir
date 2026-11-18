import { CalendarDays, Award, ShieldCheck, MapPin, Building2 } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

interface Highlight {
  icon: LucideIcon;
  label: string;
  value: string;
}

const highlights: Highlight[] = [
  { icon: CalendarDays, label: 'Didirikan', value: '2 Mei 1973' },
  { icon: Award, label: 'Akreditasi', value: 'A' },
  { icon: ShieldCheck, label: 'Naungan', value: 'TNI AU' },
  { icon: MapPin, label: 'Lokasi', value: 'Colomadu' },
];

export default function About() {
  return (
    <section id="about" className="py-16 bg-white scroll-mt-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          {/* Teks tentang sekolah */}
          <div>
            <h2 className="text-4xl font-bold text-gray-900 mb-6">Tentang SMP Angkasa Adi Soemarmo</h2>
            <p className="text-lg text-gray-700 mb-6 leading-relaxed">
              SMP Angkasa Adi Soemarmo didirikan pada <strong>2 Mei 1973</strong> oleh{' '}
              <strong>Kolonel PLLO Soeharto</strong>. Sekolah ini telah meraih{' '}
              <strong>akreditasi A</strong> dan berada di bawah naungan TNI Angkatan Udara.
            </p>

            <ul className="space-y-4">
              <li className="flex gap-3">
                <div className="bg-blue-100 w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                  <MapPin className="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <p className="font-semibold text-gray-900">Lokasi</p>
                  <p className="text-gray-600">
                    Jl. Adi Soemarmo, Malangjiwan, Kec. Colomadu, Kab. Karanganyar, Jawa Tengah
                  </p>
                </div>
              </li>
              <li className="flex gap-3">
                <div className="bg-blue-100 w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                  <Building2 className="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <p className="font-semibold text-gray-900">Naungan</p>
                  <p className="text-gray-600">
                    TNI Angkatan Udara dan Yayasan Ardhya Garini (Yasarini) Cabang Lanud Adi Soemarmo
                  </p>
                </div>
              </li>
            </ul>
          </div>

          {/* Kotak ringkasan */}
          <div className="grid grid-cols-2 gap-6">
            {highlights.map((item) => {
              const Icon = item.icon;
              return (
                <div
                  key={item.label}
                  className="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-lg text-center"
                >
                  <Icon className="w-10 h-10 text-blue-600 mx-auto mb-3" />
                  <div className="text-2xl font-bold text-gray-900 mb-1">{item.value}</div>
                  <div className="text-gray-600">{item.label}</div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </section>
  );
}