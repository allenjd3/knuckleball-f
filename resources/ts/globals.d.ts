type AlpinePrimitive = string | number | boolean | null | undefined;

type AlpineValue =
    | AlpinePrimitive
    | AlpinePrimitive[]
    | { [key: string]: AlpineValue }
    | ((...args: unknown[]) => unknown);

type AlpineType = {
    data<T extends Record<string, AlpineValue>>(
        name: string,
        callback: (this: T & AlpineMagicProperties) => T
    ): void;
}

declare global {
    var Alpine: AlpineType
}

export interface AlpineMagicProperties {
    $refs: Record<string, HTMLElement>;
}