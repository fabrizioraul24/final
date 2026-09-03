import React, { useEffect, useRef, useState } from 'react';

function useChart(canvasRef, configFactory, deps, enabled) {
    useEffect(() => {
        if (!enabled || !canvasRef.current) {
            return undefined;
        }

        let chartInstance;
        let cancelled = false;

        import('chart.js/auto').then(({ default: Chart }) => {
            if (cancelled || !canvasRef.current) {
                return;
            }

            chartInstance = new Chart(canvasRef.current, configFactory());
        });

        return () => {
            cancelled = true;
            if (chartInstance) {
                chartInstance.destroy();
            }
        };
    }, [enabled, ...deps]); // eslint-disable-line react-hooks/exhaustive-deps
}

export default function LazyChart({ configFactory, deps = [], minHeight = 238, canvasStyle }) {
    const containerRef = useRef(null);
    const canvasRef = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (!containerRef.current || visible) {
            return undefined;
        }

        const observer = new IntersectionObserver(([entry]) => {
            if (entry.isIntersecting) {
                setVisible(true);
                observer.disconnect();
            }
        }, { rootMargin: '180px' });

        observer.observe(containerRef.current);

        return () => observer.disconnect();
    }, [visible]);

    useChart(canvasRef, configFactory, deps, visible);

    return (
        <div ref={containerRef} className="exec-chart-wrap">
            {visible
                ? <canvas ref={canvasRef} style={canvasStyle} />
                : <div className="chart-skeleton" style={{ minHeight }} />}
        </div>
    );
}
